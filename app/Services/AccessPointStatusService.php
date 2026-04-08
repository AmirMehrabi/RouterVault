<?php

namespace App\Services;

use App\Models\AccessPoint;
use App\Models\AccessPointStatusChange;
use App\Services\RouterOs\AccessPointDataService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class AccessPointStatusService
{
    public function __construct(
        public AccessPointDataService $accessPointDataService,
        public WirelessClientTrackingService $wirelessClientTrackingService
    ) {}

    /**
     * @return array{
     *     online: bool,
     *     status: string,
     *     reason: string|null,
     *     collected_at: string,
     *     resource: array<string, mixed>,
     *     wireless: array<string, mixed>|null,
     *     clients: array<int, array<string, mixed>>,
     *     metrics: array<string, mixed>,
     *     access_point: AccessPoint
     * }
     */
    public function refresh(AccessPoint $accessPoint): array
    {
        $payload = $this->accessPointDataService->fetch($accessPoint);
        $checkedAt = now();
        $previousStatus = $accessPoint->status;
        $currentStatus = $payload['status'];
        $metrics = $payload['metrics'];
        $trackedClients = [];

        $attributes = [
            'status' => $currentStatus,
            'board_name' => $payload['online'] ? ($metrics['board_name'] ?? $accessPoint->board_name) : $accessPoint->board_name,
            'uptime' => $payload['online'] ? ($metrics['uptime'] ?? $accessPoint->uptime) : null,
            'cpu_usage' => $payload['online'] ? ($metrics['cpu_usage'] ?? $accessPoint->cpu_usage) : 0,
            'cpu_count' => $payload['online'] ? ($metrics['cpu_count'] ?? $accessPoint->cpu_count) : $accessPoint->cpu_count,
            'cpu_frequency' => $payload['online'] ? ($metrics['cpu_frequency'] ?? $accessPoint->cpu_frequency) : $accessPoint->cpu_frequency,
            'memory_usage' => $payload['online'] ? ($metrics['memory_usage'] ?? $accessPoint->memory_usage) : 0,
            'total_memory' => $payload['online'] ? ($metrics['total_memory'] ?? $accessPoint->total_memory) : $accessPoint->total_memory,
            'free_memory' => $payload['online'] ? ($metrics['free_memory'] ?? $accessPoint->free_memory) : $accessPoint->free_memory,
            'total_hdd_space' => $payload['online'] ? ($metrics['total_hdd_space'] ?? $accessPoint->total_hdd_space) : $accessPoint->total_hdd_space,
            'free_hdd_space' => $payload['online'] ? ($metrics['free_hdd_space'] ?? $accessPoint->free_hdd_space) : $accessPoint->free_hdd_space,
            'connected_clients_count' => $payload['online'] ? ($metrics['connected_clients_count'] ?? 0) : 0,
            'signal_quality' => $payload['online'] ? ($metrics['signal_quality'] ?? $accessPoint->signal_quality) : 0,
            'firmware_version' => $payload['online'] ? ($metrics['firmware_version'] ?? $accessPoint->firmware_version) : $accessPoint->firmware_version,
            'architecture_name' => $payload['online'] ? ($metrics['architecture_name'] ?? $accessPoint->architecture_name) : $accessPoint->architecture_name,
            'platform' => $payload['online'] ? ($metrics['platform'] ?? $accessPoint->platform) : $accessPoint->platform,
            'ssid' => $payload['online'] ? ($metrics['ssid'] ?? $accessPoint->ssid) : $accessPoint->ssid,
            'band' => $payload['online'] ? ($metrics['band'] ?? $accessPoint->band) : $accessPoint->band,
            'channel' => $payload['online'] ? ($metrics['channel'] ?? $accessPoint->channel) : $accessPoint->channel,
            'frequency' => $payload['online'] ? ($metrics['frequency'] ?? $accessPoint->frequency) : $accessPoint->frequency,
            'tx_power' => $payload['online'] ? ($metrics['tx_power'] ?? $accessPoint->tx_power) : $accessPoint->tx_power,
            'noise_floor' => $payload['online'] ? ($metrics['noise_floor'] ?? $accessPoint->noise_floor) : $accessPoint->noise_floor,
            'channel_utilization' => $payload['online'] ? ($metrics['channel_utilization'] ?? $accessPoint->channel_utilization) : $accessPoint->channel_utilization,
            'last_seen_at' => $payload['online'] ? $checkedAt : $accessPoint->last_seen_at,
        ];

        DB::transaction(function () use ($accessPoint, $attributes, $payload, $previousStatus, $currentStatus, $checkedAt, $metrics, &$trackedClients): void {
            $accessPoint->forceFill($attributes)->save();

            if ($payload['online']) {
                $trackedClients = $this->wirelessClientTrackingService->syncFromAccessPoint($accessPoint, $payload['clients']);
            } else {
                $accessPoint->wirelessClients()->update(['is_connected' => false]);
            }

            if ($previousStatus !== $currentStatus) {
                AccessPointStatusChange::create([
                    'tenant_id' => $accessPoint->tenant_id,
                    'access_point_id' => $accessPoint->id,
                    'previous_status' => $previousStatus,
                    'current_status' => $currentStatus,
                    'reason' => $payload['reason'],
                    'checked_at' => $checkedAt,
                    'meta' => [
                        'resource' => $payload['resource'],
                        'wireless' => $payload['wireless'],
                        'clients_count' => count($payload['clients']),
                        'metrics' => $metrics,
                    ],
                ]);
            }
        });

        return $payload + [
            'clients' => $trackedClients !== [] || $payload['online'] ? $trackedClients : $payload['clients'],
            'access_point' => $accessPoint->fresh(['router:id,name', 'site:id,name']),
        ];
    }

    public function latestStatusSummary(AccessPoint $accessPoint): array
    {
        $accessPoint->loadMissing([
            'router:id,name',
            'site:id,name',
            'statusChanges' => fn ($query) => $query->limit(10),
        ]);

        return [
            'status_history' => $accessPoint->statusChanges->map(function (AccessPointStatusChange $change): array {
                return [
                    'previous_status' => $change->previous_status,
                    'current_status' => $change->current_status,
                    'reason' => $change->reason,
                    'checked_at' => $this->formatCheckedAt($change->checked_at),
                ];
            })->values()->all(),
        ];
    }

    protected function formatCheckedAt(?CarbonInterface $checkedAt): ?string
    {
        return $checkedAt?->toIso8601String();
    }
}
