<?php

namespace App\Services;

use App\Models\AccessPoint;
use App\Models\AccessPointStatusChange;
use App\Services\RouterOs\AccessPointDataService;
use Carbon\CarbonInterface;

class AccessPointStatusService
{
    public function __construct(
        public AccessPointDataService $accessPointDataService
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

        $attributes = [
            'status' => $currentStatus,
            'uptime' => $payload['online'] ? ($metrics['uptime'] ?? $accessPoint->uptime) : null,
            'cpu_usage' => $payload['online'] ? ($metrics['cpu_usage'] ?? $accessPoint->cpu_usage) : 0,
            'memory_usage' => $payload['online'] ? ($metrics['memory_usage'] ?? $accessPoint->memory_usage) : 0,
            'connected_clients_count' => $payload['online'] ? ($metrics['connected_clients_count'] ?? 0) : 0,
            'signal_quality' => $payload['online'] ? ($metrics['signal_quality'] ?? $accessPoint->signal_quality) : 0,
            'firmware_version' => $payload['online'] ? ($metrics['firmware_version'] ?? $accessPoint->firmware_version) : $accessPoint->firmware_version,
            'ssid' => $payload['online'] ? ($metrics['ssid'] ?? $accessPoint->ssid) : $accessPoint->ssid,
            'band' => $payload['online'] ? ($metrics['band'] ?? $accessPoint->band) : $accessPoint->band,
            'channel' => $payload['online'] ? ($metrics['channel'] ?? $accessPoint->channel) : $accessPoint->channel,
            'frequency' => $payload['online'] ? ($metrics['frequency'] ?? $accessPoint->frequency) : $accessPoint->frequency,
            'tx_power' => $payload['online'] ? ($metrics['tx_power'] ?? $accessPoint->tx_power) : $accessPoint->tx_power,
            'noise_floor' => $payload['online'] ? ($metrics['noise_floor'] ?? $accessPoint->noise_floor) : $accessPoint->noise_floor,
            'channel_utilization' => $payload['online'] ? ($metrics['channel_utilization'] ?? $accessPoint->channel_utilization) : $accessPoint->channel_utilization,
            'last_seen_at' => $payload['online'] ? $checkedAt : $accessPoint->last_seen_at,
        ];

        $accessPoint->forceFill($attributes)->save();

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
                ],
            ]);
        }

        return $payload + [
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
