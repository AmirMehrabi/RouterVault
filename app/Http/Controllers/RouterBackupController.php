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
            'previousBackup' => $backup->previousBackup,
            'previousHistoryBackup' => RouterBackup::query()
                ->where('router_id', $backup->router_id)
                ->whereNotNull('path')
                ->where('id', '<', $backup->id)
                ->latest('id')
                ->first(),
            'nextHistoryBackup' => RouterBackup::query()
                ->where('router_id', $backup->router_id)
                ->whereNotNull('path')
                ->where('id', '>', $backup->id)
                ->oldest('id')
                ->first(),
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
                ->whereNotNull('path')
                ->with(['diff:id,router_backup_id,added_lines,removed_lines', 'artifacts:id,router_backup_id,type,status'])
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
        $routers = Router::query()
            ->whereHas('backups', fn ($query) => $query
                ->whereIn('status', ['success', 'partial_success'])
                ->whereNotNull('path'))
            ->orderBy('name')
            ->get(['id', 'name', 'version']);
        $diff = null;
        $baseBackup = null;
        $comparisonBackup = null;
        $routerId = $request->integer('router_id');
        $baseBackupId = $request->integer('old_backup_id');
        $comparisonBackupId = $request->integer('new_backup_id');

        if ($routerId && ! $baseBackupId && ! $comparisonBackupId) {
            $latest = RouterBackup::query()
                ->where('router_id', $routerId)
                ->whereIn('status', ['success', 'partial_success'])
                ->whereNotNull('path')
                ->latest()
                ->limit(2)
                ->get();
            $comparisonBackupId = $latest->get(0)?->id;
            $baseBackupId = $latest->get(1)?->id;
        }

        if ($baseBackupId && $comparisonBackupId) {
            $baseBackup = RouterBackup::query()->with(['router:id,name', 'schedule:id,name', 'diff'])->findOrFail($baseBackupId);
            $comparisonBackup = RouterBackup::query()->with(['router:id,name', 'schedule:id,name', 'diff'])->findOrFail($comparisonBackupId);
            $this->authorizeTenant($baseBackup->tenant_id);
            $this->authorizeTenant($comparisonBackup->tenant_id);
            abort_unless($baseBackup->router_id === $comparisonBackup->router_id, 403);
            abort_unless($baseBackup->path && $comparisonBackup->path, 404);
            abort_unless(Storage::disk($baseBackup->disk)->exists($baseBackup->path), 404);
            abort_unless(Storage::disk($comparisonBackup->disk)->exists($comparisonBackup->path), 404);

            $diff = $diffService->diff(
                Storage::disk($baseBackup->disk)->get($baseBackup->path),
                Storage::disk($comparisonBackup->disk)->get($comparisonBackup->path)
            );
        }

        return view('backups.compare', compact(
            'routers',
            'diff',
            'baseBackup',
            'comparisonBackup',
            'routerId',
            'baseBackupId',
            'comparisonBackupId'
        ));
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
            'routeros_version' => $backup->routeros_version,
            'changed' => $backup->changed,
            'size_bytes' => $backup->size_bytes,
            'checksum' => $backup->checksum,
            'source' => $backup->schedule?->name ?? 'Manual',
            'added_lines' => $backup->diff?->added_lines ?? 0,
            'removed_lines' => $backup->diff?->removed_lines ?? 0,
            'artifact_types' => $backup->artifacts?->where('status', 'success')->pluck('type')->values()->all() ?? [],
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
