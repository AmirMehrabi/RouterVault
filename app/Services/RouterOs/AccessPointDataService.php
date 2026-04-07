<?php

namespace App\Services\RouterOs;

use App\Models\AccessPoint;
use Illuminate\Support\Arr;
use RouterOS\Client;
use RouterOS\Query;
use Throwable;

class AccessPointDataService
{
    /**
     * @return array{
     *     online: bool,
     *     status: string,
     *     reason: string|null,
     *     collected_at: string,
     *     resource: array<string, mixed>,
     *     wireless: array<string, mixed>|null,
     *     clients: array<int, array<string, mixed>>,
     *     metrics: array<string, mixed>
     * }
     */
    public function fetch(AccessPoint $accessPoint): array
    {
        $accessPoint->loadMissing('router');

        $router = $accessPoint->router;

        if (! $router || ! $router->api_username || ! $router->api_password) {
            return $this->offlinePayload('Router API credentials are missing.');
        }

        if (! $accessPoint->ip_address) {
            return $this->offlinePayload('Access point IP address is missing.');
        }

        try {
            $client = new Client([
                'host' => $accessPoint->ip_address,
                'user' => $router->api_username,
                'pass' => $router->api_password,
                'port' => $router->api_port ?: 8728,
                'timeout' => $router->timeout ?: 10,
                'attempts' => 1,
                'delay' => 1,
                'socket_timeout' => max(($router->timeout ?: 10), 10),
            ]);

            $resource = $this->firstRow($client->query(new Query('/system/resource/print'))->read());
            $identity = $this->firstRow($client->query(new Query('/system/identity/print'))->read());
            $wireless = $this->detectWirelessInterface($client, $accessPoint);
            $clients = $this->readRegistrationTable($client);

            $metrics = $this->buildMetrics($accessPoint, $resource, $identity, $wireless, $clients);

            return [
                'online' => true,
                'status' => 'online',
                'reason' => null,
                'collected_at' => now()->toIso8601String(),
                'resource' => $resource,
                'wireless' => $wireless,
                'clients' => $clients,
                'metrics' => $metrics,
            ];
        } catch (Throwable $throwable) {
            return $this->offlinePayload($throwable->getMessage());
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $clients
     * @return array<string, mixed>
     */
    protected function buildMetrics(
        AccessPoint $accessPoint,
        array $resource,
        array $identity,
        ?array $wireless,
        array $clients
    ): array {
        $freeMemory = $this->toInt($resource['free-memory'] ?? null);
        $totalMemory = $this->toInt($resource['total-memory'] ?? null);
        $memoryUsage = $totalMemory > 0
            ? (int) round((($totalMemory - $freeMemory) / $totalMemory) * 100)
            : null;

        $signalValues = collect($clients)
            ->map(fn (array $client) => $this->signalPercent($client['signal-strength'] ?? null))
            ->filter(fn (?int $value) => $value !== null);

        return [
            'identity' => Arr::get($identity, 'name', $accessPoint->name),
            'board_name' => Arr::get($resource, 'board-name', $accessPoint->model),
            'firmware_version' => Arr::get($resource, 'version', $accessPoint->firmware_version),
            'uptime' => Arr::get($resource, 'uptime', $accessPoint->uptime),
            'cpu_usage' => $this->toInt(Arr::get($resource, 'cpu-load')) ?? $accessPoint->cpu_usage,
            'memory_usage' => $memoryUsage ?? $accessPoint->memory_usage,
            'connected_clients_count' => count($clients),
            'signal_quality' => $signalValues->isNotEmpty()
                ? (int) round($signalValues->avg())
                : $accessPoint->signal_quality,
            'ssid' => Arr::get($wireless, 'ssid', $accessPoint->ssid),
            'band' => $this->normalizeBand(Arr::get($wireless, 'band', $accessPoint->band)),
            'channel' => Arr::get($wireless, 'channel', $accessPoint->channel),
            'frequency' => $this->toInt(Arr::get($wireless, 'frequency')) ?? $accessPoint->frequency,
            'tx_power' => $this->toInt(Arr::get($wireless, 'tx-power')) ?? $accessPoint->tx_power,
            'noise_floor' => $this->toInt(Arr::get($wireless, 'noise-floor')) ?? $accessPoint->noise_floor,
            'channel_utilization' => $this->toInt(Arr::get($wireless, 'overall-tx-ccq')) ?? $accessPoint->channel_utilization,
            'last_seen_at' => now(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function readRegistrationTable(Client $client): array
    {
        $queries = [
            '/interface/wifi/registration-table/print',
            '/interface/wireless/registration-table/print',
        ];

        foreach ($queries as $path) {
            try {
                $response = $client->query(new Query($path))->read();

                if (is_array($response)) {
                    return $response;
                }
            } catch (Throwable) {
            }
        }

        return [];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function detectWirelessInterface(Client $client, AccessPoint $accessPoint): ?array
    {
        $queries = [
            '/interface/wifi/print',
            '/interface/wireless/print',
        ];

        foreach ($queries as $path) {
            try {
                $interfaces = $client->query(new Query($path))->read();

                if (! is_array($interfaces) || $interfaces === []) {
                    continue;
                }

                return collect($interfaces)
                    ->first(function (array $interface) use ($accessPoint): bool {
                        if (($interface['disabled'] ?? 'false') === 'true') {
                            return false;
                        }

                        if ($accessPoint->ssid && ($interface['ssid'] ?? null) === $accessPoint->ssid) {
                            return true;
                        }

                        return in_array($interface['default-name'] ?? '', ['wlan1', 'wifi1'], true)
                            || in_array($interface['name'] ?? '', ['wlan1', 'wifi1'], true);
                    }) ?? $interfaces[0];
            } catch (Throwable) {
            }
        }

        return null;
    }

    /**
     * @param  mixed  $value
     */
    protected function toInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        preg_match('/-?\d+/', (string) $value, $matches);

        return isset($matches[0]) ? (int) $matches[0] : null;
    }

    protected function signalPercent(?string $signalStrength): ?int
    {
        $dbm = $this->toInt($signalStrength);

        if ($dbm === null) {
            return null;
        }

        $dbm = max(-100, min(-50, $dbm));

        return (int) round((($dbm + 100) / 50) * 100);
    }

    /**
     * @param  array<int, array<string, mixed>>  $response
     * @return array<string, mixed>
     */
    protected function firstRow(array $response): array
    {
        return $response[0] ?? [];
    }

    protected function normalizeBand(?string $band): string
    {
        $band = strtolower((string) $band);

        return match (true) {
            str_contains($band, '2ghz') => '2.4GHz',
            str_contains($band, '5ghz') => '5GHz',
            str_contains($band, '6ghz') => '6GHz',
            default => $band ?: 'dual',
        };
    }

    /**
     * @return array{
     *     online: bool,
     *     status: string,
     *     reason: string,
     *     collected_at: string,
     *     resource: array<string, mixed>,
     *     wireless: array<string, mixed>|null,
     *     clients: array<int, array<string, mixed>>,
     *     metrics: array<string, mixed>
     * }
     */
    protected function offlinePayload(string $reason): array
    {
        return [
            'online' => false,
            'status' => 'offline',
            'reason' => $reason,
            'collected_at' => now()->toIso8601String(),
            'resource' => [],
            'wireless' => null,
            'clients' => [],
            'metrics' => [],
        ];
    }
}
