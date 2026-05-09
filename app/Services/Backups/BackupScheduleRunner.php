<?php

namespace App\Services\Backups;

use App\Models\BackupRun;
use App\Models\BackupSchedule;

class BackupScheduleRunner
{
    public function __construct(protected RouterBackupService $backupService) {}

    public function run(BackupSchedule $schedule, string $trigger = 'scheduled'): BackupRun
    {
        $routers = $schedule->routers()->get();
        $run = BackupRun::query()->create([
            'tenant_id' => $schedule->tenant_id,
            'backup_schedule_id' => $schedule->id,
            'trigger' => $trigger,
            'status' => 'running',
            'total_routers' => $routers->count(),
            'started_at' => now(),
        ]);

        $failedMessages = [];

        foreach ($routers as $router) {
            $backup = $this->backupService->create($router, $schedule, $run);

            if ($backup->status === 'success') {
                $run->increment('successful_backups');
            } else {
                $run->increment('failed_backups');
                $failedMessages[] = "{$router->name}: {$backup->error_message}";
            }
        }

        $run->refresh();
        $status = match (true) {
            $run->successful_backups === $run->total_routers => 'success',
            $run->successful_backups > 0 => 'partial_failed',
            default => 'failed',
        };

        $run->forceFill([
            'status' => $status,
            'finished_at' => now(),
            'error_summary' => $failedMessages === [] ? null : implode("\n", $failedMessages),
        ])->save();

        $schedule->forceFill([
            'last_run_at' => now(),
            'last_status' => $status,
            'next_run_at' => $schedule->calculateNextRun(),
        ])->save();

        return $run->fresh(['backups.router']);
    }
}
