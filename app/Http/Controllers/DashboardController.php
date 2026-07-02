<?php

namespace App\Http\Controllers;

use App\Models\BackupSchedule;
use App\Models\DiffAlert;
use App\Models\Router;
use App\Models\RouterBackup;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $completedBackups = RouterBackup::query()
            ->where('created_at', '>=', now()->subDay())
            ->whereIn('status', ['success', 'failed']);
        $completedBackupCount = (clone $completedBackups)->count();
        $successfulBackupCount = (clone $completedBackups)->where('status', 'success')->count();

        $totalRouters = Router::query()->count();
        $coveredRouters = Router::query()
            ->whereHas('backupSchedules', fn ($query) => $query->where('is_enabled', true))
            ->count();

        $latestFailedRouters = Router::query()
            ->whereHas('latestBackup', fn ($query) => $query->where('status', 'failed'))
            ->with(['latestBackup.schedule'])
            ->orderBy('name')
            ->get();

        $overdueSchedules = BackupSchedule::query()
            ->withCount('routers')
            ->where('is_enabled', true)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now())
            ->oldest('next_run_at')
            ->get();

        $pausedSchedules = BackupSchedule::query()
            ->withCount('routers')
            ->where('is_enabled', false)
            ->latest('updated_at')
            ->get();

        $unreadAlerts = DiffAlert::query()
            ->with('router:id,name')
            ->where('status', 'unread')
            ->latest()
            ->get();

        return view('dashboard', [
            'backupDashboard' => [
                'stats' => [
                    'success_rate' => $completedBackupCount > 0
                        ? round(($successfulBackupCount / $completedBackupCount) * 100, 1)
                        : null,
                    'successful_backups' => $successfulBackupCount,
                    'completed_backups' => $completedBackupCount,
                    'covered_routers' => $coveredRouters,
                    'total_routers' => $totalRouters,
                    'configuration_changes' => RouterBackup::query()
                        ->where('changed', true)
                        ->whereNotNull('previous_router_backup_id')
                        ->where('created_at', '>=', now()->subDays(7))
                        ->count(),
                    'unread_alerts' => $unreadAlerts->count(),
                    'high_unread_alerts' => $unreadAlerts->where('severity', 'high')->count(),
                ],
                'exceptions' => [
                    'failed_backups' => $latestFailedRouters->count(),
                    'schedule_issues' => $overdueSchedules->count() + $pausedSchedules->count(),
                    'high_severity_diffs' => $unreadAlerts->where('severity', 'high')->count(),
                ],
                'attention' => $this->attentionItems(
                    $latestFailedRouters,
                    $overdueSchedules,
                    $pausedSchedules,
                    $unreadAlerts
                ),
                'coverage' => [
                    'covered' => $coveredRouters,
                    'uncovered' => max(0, $totalRouters - $coveredRouters),
                    'total' => $totalRouters,
                    'active_schedules' => BackupSchedule::query()->where('is_enabled', true)->count(),
                    'next_schedule' => BackupSchedule::query()
                        ->where('is_enabled', true)
                        ->where('next_run_at', '>', now())
                        ->oldest('next_run_at')
                        ->first(['id', 'name', 'next_run_at']),
                ],
                'recent_backups' => RouterBackup::query()
                    ->with(['router:id,name', 'schedule:id,name,interval_value,interval_unit'])
                    ->latest()
                    ->limit(6)
                    ->get(),
                'recent_changes' => DiffAlert::query()
                    ->with('router:id,name')
                    ->latest()
                    ->limit(6)
                    ->get(),
                'routers' => Router::query()
                    ->where('is_dashboard_visible', true)
                    ->with(['latestBackup', 'backupSchedules' => fn ($query) => $query->where('is_enabled', true)])
                    ->orderBy('name')
                    ->paginate(6, ['*'], 'routers_page')
                    ->withQueryString(),
            ],
        ]);
    }

    public function data(): JsonResponse
    {
        return response()->json([
            'backups' => RouterBackup::query()
                ->with(['router:id,name', 'schedule:id,name'])
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn (RouterBackup $backup): array => [
                    'id' => $backup->id,
                    'router_name' => $backup->router?->name ?? 'Unknown',
                    'schedule_name' => $backup->schedule?->name ?? 'Manual',
                    'status' => $backup->status,
                    'changed' => $backup->changed,
                    'size_bytes' => $backup->size_bytes,
                    'finished_at' => ($backup->finished_at ?? $backup->started_at ?? $backup->created_at)?->toIso8601String(),
                    'show_url' => route('backups.show', $backup),
                    'status_url' => route('backups.status', $backup),
                ]),
        ]);
    }

    /**
     * @param  Collection<int, Router>  $failedRouters
     * @param  Collection<int, BackupSchedule>  $overdueSchedules
     * @param  Collection<int, BackupSchedule>  $pausedSchedules
     * @param  Collection<int, DiffAlert>  $unreadAlerts
     * @return Collection<int, array<string, mixed>>
     */
    protected function attentionItems(
        Collection $failedRouters,
        Collection $overdueSchedules,
        Collection $pausedSchedules,
        Collection $unreadAlerts
    ): Collection {
        $failures = $failedRouters->map(function (Router $router): array {
            $backup = $router->latestBackup;

            return [
                'type' => 'backup',
                'priority' => 1,
                'title' => $router->name,
                'summary' => $backup?->error_message ?: 'Backup failed without an error message.',
                'status' => 'Failure',
                'tone' => 'danger',
                'occurred_at' => $backup?->finished_at ?? $backup?->updated_at,
                'model' => $backup,
            ];
        });

        $alerts = $unreadAlerts->map(fn (DiffAlert $alert): array => [
            'type' => 'alert',
            'priority' => $alert->severity === 'high' ? 1 : 2,
            'title' => $alert->router?->name ?: 'Unknown router',
            'summary' => $alert->summary,
            'status' => ucfirst($alert->severity),
            'tone' => $alert->severity === 'high' ? 'danger' : 'warning',
            'occurred_at' => $alert->created_at,
            'model' => $alert,
        ]);

        $overdue = $overdueSchedules->map(fn (BackupSchedule $schedule): array => [
            'type' => 'overdue_schedule',
            'priority' => 2,
            'title' => $schedule->name,
            'summary' => 'Schedule is overdue for '.$schedule->routers_count.' router'.($schedule->routers_count === 1 ? '' : 's').'.',
            'status' => 'Overdue',
            'tone' => 'warning',
            'occurred_at' => $schedule->next_run_at,
            'model' => $schedule,
        ]);

        $paused = $pausedSchedules->map(fn (BackupSchedule $schedule): array => [
            'type' => 'paused_schedule',
            'priority' => 3,
            'title' => $schedule->name,
            'summary' => 'Schedule is paused for '.$schedule->routers_count.' router'.($schedule->routers_count === 1 ? '' : 's').'.',
            'status' => 'Paused',
            'tone' => 'neutral',
            'occurred_at' => $schedule->updated_at,
            'model' => $schedule,
        ]);

        return $failures
            ->concat($alerts)
            ->concat($overdue)
            ->concat($paused)
            ->sortBy([
                ['priority', 'asc'],
                ['occurred_at', 'desc'],
            ])
            ->take(8)
            ->values();
    }
}
