<?php

namespace App\Http\Controllers;

use App\Http\Requests\Backup\CompareBackupsRequest;
use App\Jobs\ProcessRouterBackup;
use App\Models\Router;
use App\Models\RouterBackup;
use App\Models\RouterBackupArtifact;
use App\Services\Backups\BackupDiffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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
            'successful' => (clone $base)->whereIn('status', ['success', 'partial_success'])->count(),
            'changed' => (clone $base)->where('changed', true)->count(),
            'failed' => (clone $base)->where('status', 'failed')->count(),
        ];

        return view('backups.index', compact('backups', 'stats'));
    }

    public function show(RouterBackup $backup): View
    {
        $this->authorizeTenant($backup->tenant_id);
        $backup->load(['router', 'schedule', 'previousBackup', 'diff', 'artifacts']);
        $displayDiff = $backup->diff?->hunks ?? [];

        if ($backup->path && $backup->previousBackup?->path
            && Storage::disk($backup->disk)->exists($backup->path)
            && Storage::disk($backup->previousBackup->disk)->exists($backup->previousBackup->path)) {
            $displayDiff = app(BackupDiffService::class)->diff(
                Storage::disk($backup->previousBackup->disk)->get($backup->previousBackup->path),
                Storage::disk($backup->disk)->get($backup->path)
            )['hunks'];
        }

        return view('backups.show', [
            'backup' => $backup,
            'displayDiff' => $displayDiff,
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

    public function downloadArtifact(RouterBackup $backup, RouterBackupArtifact $artifact): StreamedResponse
    {
        $this->authorizeTenant($backup->tenant_id);
        abort_unless(
            $artifact->router_backup_id === $backup->id
            && $artifact->tenant_id === $backup->tenant_id
            && $artifact->status === 'success'
            && $artifact->path
            && Storage::disk($artifact->disk)->exists($artifact->path),
            404
        );

        $extension = $artifact->type === 'binary' ? 'backup' : 'rsc';

        return Storage::disk($artifact->disk)->download($artifact->path, "router-backup-{$backup->id}.{$extension}");
    }

    public function retry(Request $request, RouterBackup $backup): JsonResponse|RedirectResponse
    {
        $this->authorizeTenant($backup->tenant_id);
        abort_unless(in_array($backup->status, ['failed', 'partial_success'], true), 422);

        $alreadyQueued = RouterBackup::query()
            ->where('router_id', $backup->router_id)
            ->whereIn('status', ['pending', 'running'])
            ->exists();

        if ($alreadyQueued) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This router already has a queued or running backup.'], 409);
            }

            return back()->with('error', 'This router already has a queued or running backup.');
        }

        $retry = RouterBackup::query()->create([
            'tenant_id' => $backup->tenant_id,
            'router_id' => $backup->router_id,
            'backup_schedule_id' => $backup->backup_schedule_id,
            'status' => 'pending',
            'disk' => 'local',
        ]);

        ProcessRouterBackup::dispatch($retry->id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Router backup retry queued.', 'backup' => $this->payload($retry->load('router', 'schedule'))], 202);
        }

        return back()->with('success', 'Router backup retry queued.');
    }

    public function forRouter(Router $router): JsonResponse
    {
        $this->authorizeTenant($router->tenant_id);

        return response()->json([
            'backups' => RouterBackup::query()
                ->where('router_id', $router->id)
                ->whereIn('status', ['success', 'partial_success'])
                ->latest()
                ->get()
                ->map(fn (RouterBackup $backup): array => $this->payload($backup)),
        ]);
    }

    public function status(RouterBackup $backup): JsonResponse
    {
        $this->authorizeTenant($backup->tenant_id);

        return response()->json(['backup' => $this->payload($backup->load('router', 'schedule'))]);
    }

    public function compare(CompareBackupsRequest $request, BackupDiffService $diffService): View
    {
        $routers = Router::query()->whereHas('backups', fn ($query) => $query->where('status', 'success'))->orderBy('name')->get(['id', 'name']);
        $diff = null;

        if ($request->filled(['old_backup_id', 'new_backup_id'])) {
            $old = RouterBackup::query()->whereIn('status', ['success', 'partial_success'])->whereNotNull('path')->findOrFail($request->integer('old_backup_id'));
            $new = RouterBackup::query()->whereIn('status', ['success', 'partial_success'])->whereNotNull('path')->findOrFail($request->integer('new_backup_id'));
            $this->authorizeTenant($old->tenant_id);
            $this->authorizeTenant($new->tenant_id);
            abort_unless($old->router_id === $new->router_id, 403);

            $diff = $diffService->diff(Storage::disk($old->disk)->get($old->path), Storage::disk($new->disk)->get($new->path));
        }

        return view('backups.compare', compact('routers', 'diff'));
    }

    /** @return array<string, mixed> */
    protected function payload(RouterBackup $backup): array
    {
        return [
            'id' => $backup->id,
            'router_id' => $backup->router_id,
            'router_name' => $backup->router?->name,
            'schedule_name' => $backup->schedule?->name ?? 'Manual',
            'status' => $backup->status,
            'changed' => $backup->changed,
            'size_bytes' => $backup->size_bytes,
            'created_at' => $backup->created_at?->toIso8601String(),
            'finished_at' => $backup->finished_at?->toIso8601String(),
            'error_message' => $backup->error_message,
            'show_url' => route('backups.show', $backup),
            'status_url' => route('backups.status', $backup),
        ];
    }

    protected function authorizeTenant(string $tenantId): void
    {
        if (auth()->user()?->tenant_id !== $tenantId) {
            abort(403);
        }
    }
}
