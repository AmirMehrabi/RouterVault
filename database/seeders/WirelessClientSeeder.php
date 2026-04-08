<?php

namespace Database\Seeders;

use App\Models\AccessPoint;
use App\Models\WirelessClient;
use Illuminate\Database\Seeder;

class WirelessClientSeeder extends Seeder
{
    public function run(): void
    {
        $accessPoint = AccessPoint::query()
            ->withoutGlobalScopes()
            ->with(['router', 'site'])
            ->orderBy('id')
            ->first();

        if (! $accessPoint || ! $accessPoint->router) {
            $this->command?->warn('WirelessClientSeeder skipped: no access point with an attached router was found.');

            return;
        }

        $tenantId = $accessPoint->tenant_id;
        $routerId = $accessPoint->router_id;
        $siteId = $accessPoint->site_id;
        $ssid = $accessPoint->ssid ?: 'SkyBase-Clients';
        $band = $accessPoint->band ?: '5GHz';

        $clients = [
            [
                'mac_address' => 'AA:BB:CC:DD:EE:01',
                'host_name' => 'front-desk-laptop',
                'comment' => 'Reception area device',
                'interface_name' => 'wlan1',
                'radio_name' => 'radio-a',
                'ssid' => $ssid,
                'band' => $band,
                'frequency' => 5180,
                'signal_strength' => -54,
                'signal_to_noise' => 34,
                'tx_rate' => '300Mbps',
                'rx_rate' => '300Mbps',
                'tx_ccq' => 92,
                'rx_ccq' => 94,
                'uptime' => '2h14m',
                'last_ip_address' => '10.10.10.21',
                'is_connected' => true,
                'first_seen_at' => now()->subDays(4),
                'last_seen_at' => now()->subMinutes(1),
                'last_moved_at' => now()->subHours(6),
            ],
            [
                'mac_address' => 'AA:BB:CC:DD:EE:02',
                'host_name' => 'warehouse-scanner',
                'comment' => 'Handheld scanner',
                'interface_name' => 'wlan1',
                'radio_name' => 'radio-a',
                'ssid' => $ssid,
                'band' => $band,
                'frequency' => 5180,
                'signal_strength' => -67,
                'signal_to_noise' => 24,
                'tx_rate' => '144.4Mbps',
                'rx_rate' => '144.4Mbps',
                'tx_ccq' => 78,
                'rx_ccq' => 80,
                'uptime' => '48m10s',
                'last_ip_address' => '10.10.10.22',
                'is_connected' => true,
                'first_seen_at' => now()->subDays(2),
                'last_seen_at' => now()->subMinutes(3),
                'last_moved_at' => null,
            ],
            [
                'mac_address' => 'AA:BB:CC:DD:EE:03',
                'host_name' => 'guest-phone',
                'comment' => 'Guest device',
                'interface_name' => 'wlan2',
                'radio_name' => 'radio-b',
                'ssid' => $ssid,
                'band' => '2.4GHz',
                'frequency' => 2412,
                'signal_strength' => -73,
                'signal_to_noise' => 18,
                'tx_rate' => '72.2Mbps',
                'rx_rate' => '72.2Mbps',
                'tx_ccq' => 61,
                'rx_ccq' => 64,
                'uptime' => '12m02s',
                'last_ip_address' => '10.10.10.23',
                'is_connected' => false,
                'first_seen_at' => now()->subDay(),
                'last_seen_at' => now()->subHours(2),
                'last_moved_at' => now()->subHours(3),
            ],
        ];

        foreach ($clients as $client) {
            WirelessClient::query()->updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'mac_address' => $client['mac_address'],
                ],
                [
                    'access_point_id' => $accessPoint->id,
                    'router_id' => $routerId,
                    'site_id' => $siteId,
                    'last_snapshot' => [
                        'seeded' => true,
                        'access_point' => $accessPoint->name,
                    ],
                    ...$client,
                ]
            );
        }

        $this->command?->info(sprintf('Seeded %d wireless clients for AP %s.', count($clients), $accessPoint->name));
    }
}
