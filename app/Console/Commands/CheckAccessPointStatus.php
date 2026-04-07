<?php

namespace App\Console\Commands;

use App\Models\AccessPoint;
use App\Services\AccessPointStatusService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:check-access-point-status')]
#[Description('Poll monitored access points and persist status transitions')]
class CheckAccessPointStatus extends Command
{
    public function __construct(
        protected AccessPointStatusService $accessPointStatusService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $accessPoints = AccessPoint::query()
            ->withoutGlobalScopes()
            ->where('enable_monitoring', true)
            ->whereNotNull('tenant_id')
            ->with(['router', 'site'])
            ->get();

        $this->info(sprintf('Checking %d access point(s)...', $accessPoints->count()));

        foreach ($accessPoints as $accessPoint) {
            $payload = $this->accessPointStatusService->refresh($accessPoint);

            $this->line(sprintf(
                '[%s] %s (%s)%s',
                strtoupper($payload['status']),
                $accessPoint->name,
                $accessPoint->ip_address ?: 'no-ip',
                $payload['reason'] ? ' - '.$payload['reason'] : ''
            ));
        }

        return self::SUCCESS;
    }
}
