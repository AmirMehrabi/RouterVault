<?php

namespace App\Http\Controllers;

use App\Models\RouterBackup;
use App\Services\Backups\BackupDiffService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RouterBackupController extends Controller
{
    public function index(): View
    {
        $backups = RouterBackup::query()->with(['router:id,name,ip_address', 'schedule:id,name'])->latest()->paginate(25);
        $base = RouterBackup::query();
        $stats = [
            'total' => (clone $base)->count(),
            'successful' => (clone $base)->where('status', 'success')->count(),
            'changed' => (clone $base)->where('changed', true)->count(),
            'failed' => (clone $base)->where('status', 'failed')->count(),
        ];

        return view('backups.index', compact('backups', 'stats'));
    }

    public function show(RouterBackup $backup): View
    {
        $this->authorizeTenant($backup->tenant_id);

        return view('backups.show', [
            'backup' => $backup->load(['router', 'schedule', 'previousBackup', 'diff']),
            'preview' => $backup->path && Storage::disk($backup->disk)->exists($backup->path)
                ? Str::of(Storage::disk($backup->disk)->get($backup->path))->limit(12000)->toString()
                : '',
        ]);
    }

    public function download(RouterBackup $backup): StreamedResponse
    {
        $this->authorizeTenant($backup->tenant_id);
        abort_unless($backup->status === 'success' && $backup->path && Storage::disk($backup->disk)->exists($backup->path), 404);

        return Storage::disk($backup->disk)->download($backup->path, "router-backup-{$backup->id}.rsc");
    }

    public function compare(Request $request, BackupDiffService $diffService): View
    {
        $backups = RouterBackup::query()->with('router:id,name')->where('status', 'success')->latest()->limit(100)->get();
        $diff = null;

        if ($request->filled(['old_backup_id', 'new_backup_id'])) {
            $old = RouterBackup::query()->where('status', 'success')->findOrFail($request->integer('old_backup_id'));
            $new = RouterBackup::query()->where('status', 'success')->findOrFail($request->integer('new_backup_id'));
            $this->authorizeTenant($old->tenant_id);
            $this->authorizeTenant($new->tenant_id);
            abort_unless($old->router_id === $new->router_id, 403);

            $diff = $diffService->diff(Storage::disk($old->disk)->get($old->path), Storage::disk($new->disk)->get($new->path));
        }

        return view('backups.compare', compact('backups', 'diff'));
    }

    protected function authorizeTenant(string $tenantId): void
    {
        if (auth()->user()?->tenant_id !== $tenantId) {
            abort(403);
        }
    }
}
