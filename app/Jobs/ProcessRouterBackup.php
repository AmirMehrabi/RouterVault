<?php

namespace App\Jobs;

use App\Models\RouterBackup;
use App\Services\Backups\RouterBackupService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessRouterBackup implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $routerBackupId) {}

    public function handle(RouterBackupService $routerBackupService): void
    {
        $backup = RouterBackup::query()
            ->withoutGlobalScopes()
            ->findOrFail($this->routerBackupId);

        if ($backup->status !== 'pending') {
            return;
        }

        $routerBackupService->process($backup);
    }
}
