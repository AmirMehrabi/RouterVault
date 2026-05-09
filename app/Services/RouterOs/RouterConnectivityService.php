<?php

namespace App\Services\RouterOs;

use App\Models\Router;
use Illuminate\Support\Facades\Log;
use RouterOS\Query;
use Throwable;

class RouterConnectivityService
{
    public function __construct(protected RouterOsClientFactory $clientFactory) {}

    public function check(Router $router): bool
    {
        $startedAt = microtime(true);
        $context = $this->logContext($router);

        Log::info('Router connectivity check started.', $context);
        Log::debug('Router connectivity API client configuration resolved.', $context + [
            'api_config' => $this->safeRouterOsConfig($router),
        ]);

        $diagnosticWarnings = $this->diagnosticWarnings($router);

        if ($diagnosticWarnings !== []) {
            Log::warning('Router connectivity check has configuration warnings before API connection.', $context + [
                'warnings' => $diagnosticWarnings,
            ]);
        }

        try {
            Log::debug('Router connectivity creating RouterOS API client.', $context);
            $client = $this->clientFactory->make($router);

            Log::debug('Router connectivity querying RouterOS resource endpoint.', $context + [
                'query' => '/system/resource/print',
            ]);

            $queryStartedAt = microtime(true);
            $resource = $client->query(new Query('/system/resource/print'))->read();
            $queryDurationMs = $this->durationMs($queryStartedAt);
            $resourceRows = is_array($resource) ? count($resource) : null;
            $firstResourceRow = is_array($resource) ? ($resource[0] ?? []) : [];
            $version = is_array($resource) ? ($resource[0]['version'] ?? null) : null;

            Log::info('Router connectivity RouterOS resource query succeeded.', $context + [
                'duration_ms' => $queryDurationMs,
                'resource_rows' => $resourceRows,
                'version' => $version,
                'board_name' => $firstResourceRow['board-name'] ?? null,
                'uptime' => $firstResourceRow['uptime'] ?? null,
            ]);

            $previousStatus = $router->status;

            $router->forceFill([
                'status' => 'online',
                'version' => $version ?? $router->version,
                'last_checked_at' => now(),
                'last_connected_at' => now(),
                'last_error' => null,
            ])->save();

            Log::info('Router connectivity check marked router online.', $context + [
                'previous_status' => $previousStatus,
                'new_status' => 'online',
                'duration_ms' => $this->durationMs($startedAt),
                'last_checked_at' => $router->last_checked_at?->toIso8601String(),
                'last_connected_at' => $router->last_connected_at?->toIso8601String(),
            ]);

            return true;
        } catch (Throwable $throwable) {
            $previousStatus = $router->status;

            Log::error('Router connectivity check failed before status update.', $context + [
                'previous_status' => $previousStatus,
                'duration_ms' => $this->durationMs($startedAt),
                'exception_class' => $throwable::class,
                'exception_code' => $throwable->getCode(),
                'exception_message' => $throwable->getMessage(),
                'exception_file' => $throwable->getFile(),
                'exception_line' => $throwable->getLine(),
                'exception' => $throwable,
            ]);

            $router->forceFill([
                'status' => 'offline',
                'last_checked_at' => now(),
                'last_error' => $throwable->getMessage(),
            ])->save();

            Log::warning('Router connectivity check marked router offline.', $context + [
                'previous_status' => $previousStatus,
                'new_status' => 'offline',
                'duration_ms' => $this->durationMs($startedAt),
                'last_checked_at' => $router->last_checked_at?->toIso8601String(),
                'last_error' => $router->last_error,
            ]);

            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function logContext(Router $router): array
    {
        return [
            'router_id' => $router->id,
            'tenant_id' => $router->tenant_id,
            'router_name' => $router->name,
            'router_status' => $router->status,
            'host' => $router->ip_address,
            'api_port' => (int) ($router->api_port ?: 8728),
            'use_ssl' => (bool) $router->use_ssl,
            'legacy_login' => (bool) $router->legacy_login,
            'database_timeout' => $router->timeout,
            'username_present' => filled($router->resolvedApiUsername()),
            'password_present' => filled($router->resolvedApiPassword()),
            'credential_source' => $router->password_manager_credential_id ? 'password_manager' : 'router_columns',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function safeRouterOsConfig(Router $router): array
    {
        $config = $router->routerOsConfig();

        unset($config['pass']);

        $config['user_present'] = filled($config['user'] ?? null);
        unset($config['user']);

        $config['ssh_private_key_present'] = filled($config['ssh_private_key'] ?? null);
        unset($config['ssh_private_key']);

        return $config;
    }

    /**
     * @return array<int, string>
     */
    protected function diagnosticWarnings(Router $router): array
    {
        $warnings = [];
        $config = $router->routerOsConfig();

        if (blank($router->ip_address)) {
            $warnings[] = 'Router IP address is missing.';
        }

        if (blank($router->resolvedApiUsername())) {
            $warnings[] = 'RouterOS API username is missing.';
        }

        if (blank($router->resolvedApiPassword())) {
            $warnings[] = 'RouterOS API password is missing.';
        }

        if ((bool) $router->use_ssl && (int) ($router->api_port ?: 8728) === 8728) {
            $warnings[] = 'SSL is enabled but the API port is 8728, which is commonly the non-SSL RouterOS API port.';
        }

        if (! (bool) $router->use_ssl && (int) ($router->api_port ?: 8728) === 8729) {
            $warnings[] = 'SSL is disabled but the API port is 8729, which is commonly the SSL RouterOS API port.';
        }

        if ($router->timeout !== null && (int) $router->timeout !== (int) ($config['timeout'] ?? 0)) {
            $warnings[] = 'Router timeout column does not match the RouterOS API client timeout currently being used.';
        }

        return $warnings;
    }

    protected function durationMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
