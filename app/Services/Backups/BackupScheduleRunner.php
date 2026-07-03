<?php

namespace App\Services\Backups;

use App\Models\BackupRun;
use App\Models\BackupSchedule;

class BackupScheduleRunner
{
    public function __construct(protected RouterBackupService $backupService) {}

    public function run(BackupSchedule $schedule, string $trigger = 'scheduled'): BackupRun
    {
        return $this->process($this->prepare($schedule, $trigger));
    }

    public function prepare(BackupSchedule $schedule, string $trigger = 'manual'): BackupRun
    {
        return BackupRun::query()->create([
            'tenant_id' => $schedule->tenant_id,
            'backup_schedule_id' => $schedule->id,
            'trigger' => $trigger,
            'status' => 'queued',
        ]);
    }

    public function process(BackupRun $run): BackupRun
    {
        $run->loadMissing('schedule');
        $schedule = $run->schedule;

        if ($schedule === null || $schedule->tenant_id !== $run->tenant_id) {
            throw new \RuntimeException('Backup run schedule does not belong to the same tenant.');
        }

        $routers = $schedule->routers()
            ->where('routers.tenant_id', $run->tenant_id)
            ->where(function ($query): void {
                $query->where('routers.backup_rsc_enabled', true)
                    ->orWhere('routers.backup_binary_enabled', true);
            })
            ->get();
        $run->forceFill([
            'status' => 'running',
            'total_routers' => $routers->count(),
            'successful_backups' => 0,
            'failed_backups' => 0,
            'started_at' => now(),
            'finished_at' => null,
            'error_summary' => null,
        ])->save();

        $failedMessages = [];

        foreach ($routers as $router) {
            $backup = $this->backupService->create($router, $schedule, $run);

            if (in_array($backup->status, ['success', 'partial_success'], true)) {
                $run->increment('successful_backups');

                if ($backup->status === 'partial_success') {
                    $failedMessages[] = "{$router->name}: {$backup->error_message}";
                }
            } else {
                $run->increment('failed_backups');
                $failedMessages[] = "{$router->name}: {$backup->error_message}";
            }
        }

        $run->refresh();
        $status = match (true) {
            $failedMessages !== [] && $run->successful_backups > 0 => 'partial_failed',
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
