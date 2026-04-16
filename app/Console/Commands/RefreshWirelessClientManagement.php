<?php

namespace App\Console\Commands;

use App\Services\RouterOs\WirelessClientManagementService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:refresh-wireless-client-management {--limit=0 : Limit the number of radios processed in this run}')]
#[Description('Refresh discovery data for manageable MikroTik wireless clients')]
class RefreshWirelessClientManagement extends Command
{
    public function __construct(
        protected WirelessClientManagementService $wirelessClientManagementService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $limit = max(0, (int) $this->option('limit'));
        $processed = $this->wirelessClientManagementService->refreshManageableClients($limit === 0 ? null : $limit);

        $this->info(sprintf('Refreshed management discovery for %d wireless client(s).', $processed));

        return self::SUCCESS;
    }
}
