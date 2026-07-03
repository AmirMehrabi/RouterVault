<?php

namespace App\Console\Commands;

use App\Jobs\RecordQueueHeartbeat;
use App\Models\SystemHeartbeat;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('system:heartbeat')]
#[Description('Command description')]
class RecordSchedulerHeartbeat extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        SystemHeartbeat::record('scheduler');
        RecordQueueHeartbeat::dispatch();

        $this->info('Scheduler heartbeat recorded and queue heartbeat dispatched.');

        return self::SUCCESS;
    }
}
