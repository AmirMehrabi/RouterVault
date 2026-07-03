<?php

namespace App\Http\Controllers;

use App\Http\Requests\BackupSchedule\FilterScheduleBackupsRequest;
use App\Http\Requests\BackupSchedule\StoreBackupScheduleRequest;
use App\Http\Requests\BackupSchedule\UpdateBackupScheduleRequest;
use App\Jobs\ProcessBackupScheduleRun;
use App\Models\BackupRun;
use App\Models\BackupSchedule;
use App\Models\Router;
use App\Models\RouterBackup;
use App\Services\Backups\BackupScheduleRunner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BackupScheduleController extends Controller
{
    public function index(): View
    {
        $schedules = BackupSchedule::query()->withCount('routers')->latest()->get();
        $stats = [
            'active' => $schedules->where('is_enabled', true)->count(),
            'paused' => $schedules->where('is_enabled', false)->count(),
            'routers' => $schedules->sum('routers_count'),
            'dueSoon' => $schedules->where('is_enabled', true)->filter(fn (BackupSchedule $schedule): bool => $schedule->next_run_at?->lte(now()->addHour()) ?? false)->count(),
        ];

        return view('schedules.index', compact('schedules', 'stats'));
    }

    public function create(): View
    {
        return view('schedules.create', ['schedule' => new BackupSchedule, 'routers' => $this->routers()]);
    }

    public function store(StoreBackupScheduleRequest $request): RedirectResponse
    {
        $schedule = BackupSchedule::query()->create($this->payload($request->validated()));
        $schedule->routers()->sync($request->validated('router_ids'));

        return redirect()->route('schedules.show', $schedule)->with('success', 'Backup schedule created.');
    }

    public function show(FilterScheduleBackupsRequest $request, BackupSchedule $schedule): View
    {
        $this->authorizeTenant($schedule->tenant_id);

        $backups = RouterBackup::query()
            ->with('router:id,name')
            ->where('backup_schedule_id', $schedule->id)
            ->when($request->integer('router_id'), fn ($query, $routerId) => $query->where('router_id', $routerId))
            ->when($request->string('status')->value(), fn ($query, $status) => $query->where('status', $status))
            ->when($request->filled('changed'), fn ($query) => $query->where('changed', $request->boolean('changed')))
            ->when($request->date('from'), fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($request->date('to'), fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->latest()
            ->paginate(10, ['*'], 'backups_page')
            ->withQueryString();

        return view('schedules.show', [
            'schedule' => $schedule->load(['routers', 'runs' => fn ($query) => $query->latest()->limit(10)]),
            'backups' => $backups,
            'backupStats' => [
                'total' => $schedule->backups()->count(),
                'success' => $schedule->backups()->whereIn('status', ['success', 'partial_success'])->count(),
                'failed' => $schedule->backups()->where('status', 'failed')->count(),
                'active' => $schedule->backups()->whereIn('status', ['pending', 'running'])->count(),
            ],
        ]);
    }

    public function edit(BackupSchedule $schedule): View
    {
        $this->authorizeTenant($schedule->tenant_id);

        return view('schedules.edit', ['schedule' => $schedule->load('routers'), 'routers' => $this->routers()]);
    }

    public function update(UpdateBackupScheduleRequest $request, BackupSchedule $schedule): RedirectResponse
    {
        $this->authorizeTenant($schedule->tenant_id);
        $schedule->update($this->payload($request->validated()));
        $schedule->routers()->sync($request->validated('router_ids'));

        return redirect()->route('schedules.show', $schedule)->with('success', 'Backup schedule updated.');
    }

    public function destroy(BackupSchedule $schedule): RedirectResponse
    {
        $this->authorizeTenant($schedule->tenant_id);
        $schedule->delete();

        return redirect()->route('schedules.index')->with('success', 'Backup schedule deleted.');
    }

    public function run(Request $request, BackupSchedule $schedule, BackupScheduleRunner $runner): JsonResponse|RedirectResponse
    {
        $this->authorizeTenant($schedule->tenant_id);

        $alreadyQueued = BackupRun::query()
            ->where('backup_schedule_id', $schedule->id)
            ->whereIn('status', ['queued', 'running'])
            ->exists();

        if ($alreadyQueued) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This backup schedule already has a queued or running execution.'], 409);
            }

            return back()->with('error', 'This backup schedule already has a queued or running execution.');
        }

        $run = $runner->prepare($schedule);
        ProcessBackupScheduleRun::dispatch($run->id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Backup schedule run queued.', 'run' => $this->runPayload($run)], 202);
        }

        return back()->with('success', 'Backup schedule run queued.');
    }

    public function runs(BackupSchedule $schedule): JsonResponse
    {
        $this->authorizeTenant($schedule->tenant_id);

        return response()->json([
            'runs' => $schedule->runs()->latest()->limit(10)->get()->map(fn (BackupRun $run): array => $this->runPayload($run)),
        ]);
    }

    public function toggle(BackupSchedule $schedule): RedirectResponse
    {
        $this->authorizeTenant($schedule->tenant_id);
        $schedule->update(['is_enabled' => ! $schedule->is_enabled]);

        return back()->with('success', 'Backup schedule status updated.');
    }

    protected function routers()
    {
        return Router::query()
            ->where(function ($query): void {
                $query->where('backup_rsc_enabled', true)->orWhere('backup_binary_enabled', true);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'ip_address']);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function payload(array $validated): array
    {
        unset($validated['router_ids']);
        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['is_enabled'] = (bool) ($validated['is_enabled'] ?? false);
        $validated['export_mode'] = 'full';
        $validated['next_run_at'] = $validated['next_run_at'] ?? now();

        return $validated;
    }

    protected function authorizeTenant(string $tenantId): void
    {
        if (auth()->user()?->tenant_id !== $tenantId) {
            abort(403);
        }
    }

    /** @return array<string, mixed> */
    protected function runPayload(BackupRun $run): array
    {
        return [
            'id' => $run->id,
            'status' => $run->status,
            'successful_backups' => $run->successful_backups,
            'failed_backups' => $run->failed_backups,
            'total_routers' => $run->total_routers,
            'started_at' => $run->started_at?->toIso8601String(),
            'finished_at' => $run->finished_at?->toIso8601String(),
            'error_summary' => $run->error_summary,
        ];
    }
}
