<?php

namespace App\Services;

use App\Models\AccessPoint;
use App\Models\WirelessClient;
use App\Models\WirelessClientMovement;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class WirelessClientTrackingService
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    public function syncFromAccessPoint(AccessPoint $accessPoint, array $rows): array
    {
        $syncedClients = [];
        $seenMacAddresses = [];
        $checkedAt = now();

        foreach ($rows as $row) {
            $normalized = $this->normalizeRow($row, $accessPoint);
            $macAddress = $normalized['mac_address'];

            if (! $macAddress) {
                continue;
            }

            $seenMacAddresses[] = $macAddress;

            $wirelessClient = WirelessClient::query()->firstOrNew([
                'tenant_id' => $accessPoint->tenant_id,
                'mac_address' => $macAddress,
            ]);

            $previousAccessPointId = $wirelessClient->access_point_id;
            $previousSiteId = $wirelessClient->site_id;
            $previousRouterId = $wirelessClient->router_id;
            $wasExisting = $wirelessClient->exists;

            $wirelessClient->fill([
                'access_point_id' => $accessPoint->id,
                'router_id' => $accessPoint->router_id,
                'site_id' => $accessPoint->site_id,
                'interface_name' => $normalized['interface_name'],
                'radio_name' => $normalized['radio_name'],
                'host_name' => $normalized['host_name'],
                'comment' => $normalized['comment'],
                'ssid' => $normalized['ssid'] ?: $accessPoint->ssid,
                'band' => $normalized['band'] ?: $accessPoint->band,
                'frequency' => $normalized['frequency'],
                'signal_strength' => $normalized['signal_strength'],
                'signal_to_noise' => $normalized['signal_to_noise'],
                'tx_rate' => $normalized['tx_rate'],
                'rx_rate' => $normalized['rx_rate'],
                'tx_ccq' => $normalized['tx_ccq'],
                'rx_ccq' => $normalized['rx_ccq'],
                'uptime' => $normalized['uptime'],
                'last_ip_address' => $normalized['last_ip_address'],
                'is_connected' => true,
                'first_seen_at' => $wirelessClient->first_seen_at ?? $checkedAt,
                'last_seen_at' => $checkedAt,
                'last_snapshot' => $normalized['snapshot'],
            ]);

            if ($wasExisting && $previousAccessPointId && $previousAccessPointId !== $accessPoint->id) {
                $wirelessClient->last_moved_at = $checkedAt;
            }

            $wirelessClient->save();

            if ($wasExisting && $previousAccessPointId && $previousAccessPointId !== $accessPoint->id) {
                WirelessClientMovement::create([
                    'tenant_id' => $accessPoint->tenant_id,
                    'wireless_client_id' => $wirelessClient->id,
                    'from_access_point_id' => $previousAccessPointId,
                    'to_access_point_id' => $accessPoint->id,
                    'from_site_id' => $previousSiteId,
                    'to_site_id' => $accessPoint->site_id,
                    'from_router_id' => $previousRouterId,
                    'to_router_id' => $accessPoint->router_id,
                    'moved_at' => $checkedAt,
                    'meta' => [
                        'snapshot' => $normalized['snapshot'],
                    ],
                ]);
            }

            $syncedClients[] = $this->transformWirelessClient($wirelessClient->fresh(['accessPoint:id,name', 'site:id,name']));
        }

        WirelessClient::query()
            ->where('tenant_id', $accessPoint->tenant_id)
            ->where('access_point_id', $accessPoint->id)
            ->when($seenMacAddresses !== [], function ($query) use ($seenMacAddresses) {
                $query->whereNotIn('mac_address', $seenMacAddresses);
            })
            ->when($seenMacAddresses === [], function ($query) {
                $query->whereNotNull('mac_address');
            })
            ->update([
                'is_connected' => false,
            ]);

        return $syncedClients;
    }

    public function transformWirelessClient(WirelessClient $wirelessClient): array
    {
        return [
            'id' => $wirelessClient->id,
            'mac_address' => $wirelessClient->mac_address,
            'host_name' => $wirelessClient->host_name,
            'device_identity' => $wirelessClient->device_identity,
            'device_version' => $wirelessClient->device_version,
            'pppoe_username' => $wirelessClient->pppoe_username,
            'comment' => $wirelessClient->comment,
            'interface_name' => $wirelessClient->interface_name,
            'radio_name' => $wirelessClient->radio_name,
            'ssid' => $wirelessClient->ssid,
            'band' => $wirelessClient->band,
            'frequency' => $wirelessClient->frequency,
            'signal_strength' => $wirelessClient->signal_strength,
            'signal_to_noise' => $wirelessClient->signal_to_noise,
            'tx_rate' => $wirelessClient->tx_rate,
            'rx_rate' => $wirelessClient->rx_rate,
            'tx_ccq' => $wirelessClient->tx_ccq,
            'rx_ccq' => $wirelessClient->rx_ccq,
            'uptime' => $wirelessClient->uptime,
            'last_ip_address' => $wirelessClient->last_ip_address,
            'management_ip_address' => $wirelessClient->management_ip_address,
            'is_connected' => $wirelessClient->is_connected,
            'first_seen_at' => $wirelessClient->first_seen_at?->toIso8601String(),
            'last_seen_at' => $wirelessClient->last_seen_at?->toIso8601String(),
            'last_discovered_at' => $wirelessClient->last_discovered_at?->toIso8601String(),
            'last_seen_human' => $wirelessClient->last_seen_at?->diffForHumans(),
            'last_moved_at' => $wirelessClient->last_moved_at?->toIso8601String(),
            'last_moved_human' => $wirelessClient->last_moved_at?->diffForHumans(),
            'access_point' => $wirelessClient->accessPoint?->name,
            'site' => $wirelessClient->site?->name,
            'password_manager_credential_id' => $wirelessClient->password_manager_credential_id,
            'credential_name' => $wirelessClient->passwordManagerCredential?->name,
            'credential_source' => $wirelessClient->provisioningCredentialSource(),
            'provisioning_username' => $wirelessClient->resolvedProvisioningUsername(),
            'provisioning_status' => $wirelessClient->provisioningStatusLabel(),
            'is_provisioned' => $wirelessClient->isProvisioned(),
            'is_manageable' => $wirelessClient->isMikrotikManageable(),
            'last_management_status' => $wirelessClient->last_management_status,
            'last_management_message' => $wirelessClient->last_management_message,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function normalizeRow(array $row, AccessPoint $accessPoint): array
    {
        $macAddress = $this->normalizeMac(Arr::get($row, 'mac-address') ?? Arr::get($row, 'mac_address'));

        return [
            'mac_address' => $macAddress,
            'interface_name' => Arr::get($row, 'interface') ?? Arr::get($row, 'interface-name'),
            'radio_name' => Arr::get($row, 'radio-name'),
            'host_name' => Arr::get($row, 'host-name') ?? Arr::get($row, 'host_name') ?? Arr::get($row, 'comment'),
            'comment' => Arr::get($row, 'comment'),
            'ssid' => Arr::get($row, 'ssid') ?: $accessPoint->ssid,
            'band' => Arr::get($row, 'band') ?: $accessPoint->band,
            'frequency' => $this->toInt(Arr::get($row, 'rx-chains') ? Arr::get($row, 'frequency') : Arr::get($row, 'frequency')),
            'signal_strength' => $this->toInt(Arr::get($row, 'signal-strength') ?? Arr::get($row, 'signal')),
            'signal_to_noise' => $this->toInt(Arr::get($row, 'signal-to-noise')),
            'tx_rate' => Arr::get($row, 'tx-rate') ?? Arr::get($row, 'tx_rate'),
            'rx_rate' => Arr::get($row, 'rx-rate') ?? Arr::get($row, 'rx_rate'),
            'tx_ccq' => $this->toInt(Arr::get($row, 'tx-ccq')),
            'rx_ccq' => $this->toInt(Arr::get($row, 'rx-ccq')),
            'uptime' => Arr::get($row, 'uptime'),
            'last_ip_address' => Arr::get($row, 'last-ip') ?? Arr::get($row, 'last-ip-address') ?? Arr::get($row, 'last_ip_address'),
            'snapshot' => $row,
        ];
    }

    protected function normalizeMac(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return Str::upper($value);
    }

    protected function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        preg_match('/-?\d+/', (string) $value, $matches);

        return isset($matches[0]) ? (int) $matches[0] : null;
    }
}
