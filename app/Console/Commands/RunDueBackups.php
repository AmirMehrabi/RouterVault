<?php

namespace App\Console\Commands;

use App\Models\BackupSchedule;
use App\Models\SystemHeartbeat;
use App\Services\Backups\BackupScheduleRunner;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('backups:run-due')]
#[Description('Run enabled backup schedules that are due')]
class RunDueBackups extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(BackupScheduleRunner $runner): int
    {
        $schedules = BackupSchedule::query()
            ->withoutGlobalScopes()
            ->where('is_enabled', true)
            ->where(function ($query): void {
                $query->whereNull('next_run_at')->orWhere('next_run_at', '<=', now());
            })
            ->get();

        foreach ($schedules as $schedule) {
            $runner->run($schedule);
        }

        SystemHeartbeat::record('backup-scheduler', ['due_schedules' => $schedules->count()]);

        $this->info("Ran {$schedules->count()} due backup schedule(s).");

        return self::SUCCESS;
    }
}
