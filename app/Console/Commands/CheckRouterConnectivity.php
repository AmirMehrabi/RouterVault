<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Services\RouterOs\RouterConnectivityService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('routers:check-connectivity {--router= : Check one router ID}')]
#[Description('Check RouterOS API connectivity for tenant routers')]
class CheckRouterConnectivity extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(RouterConnectivityService $connectivityService): int
    {
        $query = Router::query()->withoutGlobalScopes()->whereNotNull('tenant_id');

        if ($this->option('router')) {
            $query->whereKey($this->option('router'));
        }

        $checked = 0;
        foreach ($query->get() as $router) {
            $connectivityService->check($router);
            $checked++;
        }

        $this->info("Checked {$checked} router(s).");

        return self::SUCCESS;
    }
}
