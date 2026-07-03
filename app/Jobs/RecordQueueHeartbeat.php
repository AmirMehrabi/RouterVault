<?php

namespace App\Jobs;

use App\Models\SystemHeartbeat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordQueueHeartbeat implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        SystemHeartbeat::record('queue', ['connection' => config('queue.default')]);
    }
}
