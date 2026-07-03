<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Models\SystemHeartbeat;
use App\Services\RouterOs\RouterConnectivityService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Signature('routers:check-connectivity {--router= : Check one router ID}')]
#[Description('Check RouterOS API connectivity for tenant routers')]
class CheckRouterConnectivity extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(RouterConnectivityService $connectivityService): int
    {
        $startedAt = microtime(true);
        $routerId = $this->option('router');

        Log::info('Router connectivity command started.', [
            'command' => 'routers:check-connectivity',
            'router_option' => $routerId,
        ]);

        $query = Router::query()
            ->withoutGlobalScopes()
            ->whereNotNull('tenant_id')
            ->with('passwordManagerCredential:id,username,password');

        if ($routerId) {
            $query->whereKey($routerId);
        }

        $totalRouters = (clone $query)->count();

        Log::info('Router connectivity command query prepared.', [
            'command' => 'routers:check-connectivity',
            'router_option' => $routerId,
            'routers_to_check' => $totalRouters,
        ]);

        $checked = 0;
        $online = 0;
        $offline = 0;
        $failed = 0;

        foreach ($query->get() as $router) {
            $routerStartedAt = microtime(true);

            Log::debug('Router connectivity command dispatching router check.', [
                'router_id' => $router->id,
                'tenant_id' => $router->tenant_id,
                'router_name' => $router->name,
                'host' => $router->ip_address,
                'api_port' => (int) ($router->api_port ?: 8728),
                'previous_status' => $router->status,
            ]);

            try {
                $isOnline = $connectivityService->check($router);
                $checked++;

                if ($isOnline) {
                    $online++;
                } else {
                    $offline++;
                }

                Log::info('Router connectivity command completed router check.', [
                    'router_id' => $router->id,
                    'tenant_id' => $router->tenant_id,
                    'router_name' => $router->name,
                    'host' => $router->ip_address,
                    'result' => $isOnline ? 'online' : 'offline',
                    'duration_ms' => $this->durationMs($routerStartedAt),
                ]);
            } catch (Throwable $throwable) {
                $failed++;

                Log::error('Router connectivity command encountered an unexpected router check exception.', [
                    'router_id' => $router->id,
                    'tenant_id' => $router->tenant_id,
                    'router_name' => $router->name,
                    'host' => $router->ip_address,
                    'duration_ms' => $this->durationMs($routerStartedAt),
                    'exception_class' => $throwable::class,
                    'exception_code' => $throwable->getCode(),
                    'exception_message' => $throwable->getMessage(),
                    'exception_file' => $throwable->getFile(),
                    'exception_line' => $throwable->getLine(),
                    'exception' => $throwable,
                ]);
            }
        }

        Log::info('Router connectivity command finished.', [
            'command' => 'routers:check-connectivity',
            'router_option' => $routerId,
            'routers_to_check' => $totalRouters,
            'checked' => $checked,
            'online' => $online,
            'offline' => $offline,
            'failed' => $failed,
            'duration_ms' => $this->durationMs($startedAt),
        ]);

        SystemHeartbeat::record('router-connectivity', [
            'checked' => $checked,
            'online' => $online,
            'offline' => $offline,
            'failed' => $failed,
        ], $failed > 0 ? 'warning' : 'healthy');

        $this->info("Checked {$checked} router(s). Online: {$online}. Offline: {$offline}. Failed: {$failed}.");

        return self::SUCCESS;
    }

    protected function durationMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
