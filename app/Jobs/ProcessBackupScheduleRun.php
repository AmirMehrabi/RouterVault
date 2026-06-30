<?php

namespace App\Jobs;

use App\Models\BackupRun;
use App\Services\Backups\BackupScheduleRunner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessBackupScheduleRun implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $backupRunId) {}

    public function handle(BackupScheduleRunner $backupScheduleRunner): void
    {
        $run = BackupRun::query()
            ->withoutGlobalScopes()
            ->findOrFail($this->backupRunId);

        if ($run->status !== 'queued') {
            return;
        }

        $backupScheduleRunner->process($run);
    }
}
