<?php

namespace App\Http\Controllers;

use App\Http\Requests\BackupSchedule\StoreBackupScheduleRequest;
use App\Http\Requests\BackupSchedule\UpdateBackupScheduleRequest;
use App\Jobs\ProcessBackupScheduleRun;
use App\Models\BackupRun;
use App\Models\BackupSchedule;
use App\Models\Router;
use App\Services\Backups\BackupScheduleRunner;
use Illuminate\Http\RedirectResponse;
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

    public function show(BackupSchedule $schedule): View
    {
        $this->authorizeTenant($schedule->tenant_id);

        return view('schedules.show', [
            'schedule' => $schedule->load(['routers', 'runs' => fn ($query) => $query->latest()->limit(10)]),
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

    public function run(BackupSchedule $schedule, BackupScheduleRunner $runner): RedirectResponse
    {
        $this->authorizeTenant($schedule->tenant_id);

        $alreadyQueued = BackupRun::query()
            ->where('backup_schedule_id', $schedule->id)
            ->whereIn('status', ['queued', 'running'])
            ->exists();

        if ($alreadyQueued) {
            return back()->with('error', 'This backup schedule already has a queued or running execution.');
        }

        $run = $runner->prepare($schedule);
        ProcessBackupScheduleRun::dispatch($run->id);

        return back()->with('success', 'Backup schedule run queued.');
    }

    public function toggle(BackupSchedule $schedule): RedirectResponse
    {
        $this->authorizeTenant($schedule->tenant_id);
        $schedule->update(['is_enabled' => ! $schedule->is_enabled]);

        return back()->with('success', 'Backup schedule status updated.');
    }

    protected function routers()
    {
        return Router::query()->orderBy('name')->get(['id', 'name', 'ip_address']);
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
}
