<?php

namespace Database\Factories;

use App\Models\AccessPoint;
use App\Models\Router;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\WirelessClient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WirelessClient>
 */
class WirelessClientFactory extends Factory
{
    public function definition(): array
    {
        $tenant = Tenant::query()->inRandomOrder()->first();
        $accessPoint = AccessPoint::query()->when($tenant, fn ($query) => $query->where('tenant_id', $tenant->id))->inRandomOrder()->first();
        $router = $accessPoint?->router ?? Router::query()->when($tenant, fn ($query) => $query->where('tenant_id', $tenant->id))->inRandomOrder()->first();
        $site = $accessPoint?->site ?? Site::query()->when($tenant, fn ($query) => $query->where('tenant_id', $tenant->id))->inRandomOrder()->first();
        $connected = fake()->boolean(85);

        $provisioned = fake()->boolean(40);

        return [
            'tenant_id' => $tenant?->id,
            'password_manager_credential_id' => null,
            'access_point_id' => $accessPoint?->id,
            'router_id' => $router?->id,
            'site_id' => $site?->id,
            'mac_address' => fake()->unique()->macAddress(),
            'interface_name' => fake()->randomElement(['wlan1', 'wlan2', 'wifi1']),
            'radio_name' => fake()->optional()->randomElement(['radio-a', 'radio-b']),
            'host_name' => fake()->optional()->userName(),
            'comment' => fake()->optional()->sentence(3),
            'ssid' => $accessPoint?->ssid ?? fake()->randomElement(['SkyBase-Clients', 'Guest-WiFi']),
            'band' => $accessPoint?->band ?? fake()->randomElement(['2.4GHz', '5GHz']),
            'frequency' => fake()->randomElement([2412, 2437, 2462, 5180, 5220]),
            'signal_strength' => fake()->numberBetween(-80, -45),
            'signal_to_noise' => fake()->numberBetween(15, 45),
            'tx_rate' => fake()->randomElement(['144.4Mbps-20MHz/2S/SGI', '300Mbps-40MHz/2S/SGI']),
            'rx_rate' => fake()->randomElement(['144.4Mbps-20MHz/2S/SGI', '300Mbps-40MHz/2S/SGI']),
            'tx_ccq' => fake()->numberBetween(50, 100),
            'rx_ccq' => fake()->numberBetween(50, 100),
            'uptime' => fake()->randomElement(['5m12s', '1h02m', '2d03h']),
            'last_ip_address' => fake()->ipv4(),
            'provisioning_username' => $provisioned ? fake()->userName() : null,
            'provisioning_password' => $provisioned ? fake()->password(12, 18) : null,
            'is_connected' => $connected,
            'first_seen_at' => now()->subDays(fake()->numberBetween(1, 10)),
            'last_seen_at' => now()->subMinutes(fake()->numberBetween(1, 30)),
            'last_moved_at' => fake()->boolean(35) ? now()->subHours(fake()->numberBetween(1, 48)) : null,
            'last_snapshot' => ['source' => 'factory'],
        ];
    }
}
