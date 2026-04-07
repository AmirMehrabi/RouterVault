<?php

namespace Database\Factories;

use App\Models\AccessPoint;
use App\Models\Router;
use App\Models\Site;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessPoint>
 */
class AccessPointFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tenant = Tenant::query()->inRandomOrder()->first();
        $router = Router::query()->when($tenant, fn ($query) => $query->where('tenant_id', $tenant->id))->inRandomOrder()->first();
        $site = Site::query()->when($tenant, fn ($query) => $query->where('tenant_id', $tenant->id))->inRandomOrder()->first();
        $isOnline = fake()->boolean(85);

        return [
            'tenant_id' => $tenant?->id,
            'router_id' => $router?->id,
            'site_id' => $site?->id,
            'name' => 'AP-'.strtoupper(fake()->bothify('??-##')),
            'model' => fake()->randomElement(['cAP ax', 'wAP ax', 'Audience', 'hAP ax2', 'netMetal ac2']),
            'vendor' => fake()->randomElement(['Mikrotik', 'Ubiquiti', 'Cambium']),
            'ip_address' => '10.'.fake()->numberBetween(10, 99).'.'.fake()->numberBetween(1, 254).'.'.fake()->numberBetween(1, 254),
            'mac_address' => fake()->unique()->macAddress(),
            'ssid' => fake()->randomElement(['SkyBase-Clients', 'Tower-Backhaul', 'Guest-WiFi', 'Campus-5G']),
            'band' => fake()->randomElement(['2.4GHz', '5GHz', 'dual']),
            'channel' => (string) fake()->randomElement([1, 6, 11, 36, 44, 149]),
            'frequency' => fake()->randomElement([2412, 2437, 2462, 5180, 5220, 5745]),
            'tx_power' => fake()->numberBetween(10, 30),
            'location' => fake()->randomElement(['Rooftop sector A', 'Lobby ceiling', 'Warehouse aisle 4', 'Tower cabinet']),
            'status' => $isOnline ? 'online' : fake()->randomElement(['offline', 'maintenance']),
            'firmware_version' => fake()->randomElement(['RouterOS 7.15', 'RouterOS 7.16', 'RouterOS 7.17']),
            'uptime' => $isOnline ? fake()->randomElement(['2d 04h', '8d 13h', '21d 09h']) : null,
            'cpu_usage' => $isOnline ? fake()->numberBetween(5, 65) : 0,
            'memory_usage' => $isOnline ? fake()->numberBetween(20, 70) : 0,
            'connected_clients_count' => $isOnline ? fake()->numberBetween(0, 80) : 0,
            'signal_quality' => $isOnline ? fake()->numberBetween(45, 99) : 0,
            'noise_floor' => fake()->numberBetween(-100, -80),
            'channel_utilization' => $isOnline ? fake()->numberBetween(10, 90) : 0,
            'enable_monitoring' => true,
            'enable_provisioning' => true,
            'last_seen_at' => $isOnline ? now()->subMinutes(fake()->numberBetween(1, 15)) : now()->subHours(fake()->numberBetween(1, 12)),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
