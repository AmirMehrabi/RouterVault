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
        $totalMemory = fake()->randomElement([67108864, 134217728, 268435456]);
        $freeMemory = fake()->numberBetween((int) ($totalMemory * 0.2), $totalMemory);
        $totalHddSpace = fake()->randomElement([16777216, 33554432, 134217728]);
        $freeHddSpace = fake()->numberBetween((int) ($totalHddSpace * 0.2), $totalHddSpace);

        return [
            'tenant_id' => $tenant?->id,
            'password_manager_credential_id' => null,
            'router_id' => $router?->id,
            'site_id' => $site?->id,
            'name' => 'AP-'.strtoupper(fake()->bothify('??-##')),
            'model' => fake()->randomElement(['cAP ax', 'wAP ax', 'Audience', 'hAP ax2', 'netMetal ac2']),
            'board_name' => fake()->randomElement(['RBcAPGi-5acD2nD', 'cAPGi-5HaxD2HaxD', 'LHG 5 ac']),
            'vendor' => fake()->randomElement(['Mikrotik', 'Ubiquiti', 'Cambium']),
            'ip_address' => '10.'.fake()->numberBetween(10, 99).'.'.fake()->numberBetween(1, 254).'.'.fake()->numberBetween(1, 254),
            'api_username' => 'admin',
            'api_password' => fake()->password(12, 18),
            'mac_address' => fake()->unique()->macAddress(),
            'ssid' => fake()->randomElement(['SkyBase-Clients', 'Tower-Backhaul', 'Guest-WiFi', 'Campus-5G']),
            'band' => fake()->randomElement(['2.4GHz', '5GHz', 'dual']),
            'channel' => (string) fake()->randomElement([1, 6, 11, 36, 44, 149]),
            'frequency' => fake()->randomElement([2412, 2437, 2462, 5180, 5220, 5745]),
            'tx_power' => fake()->numberBetween(10, 30),
            'location' => fake()->randomElement(['Rooftop sector A', 'Lobby ceiling', 'Warehouse aisle 4', 'Tower cabinet']),
            'status' => $isOnline ? 'online' : fake()->randomElement(['offline', 'maintenance']),
            'firmware_version' => fake()->randomElement(['RouterOS 7.15', 'RouterOS 7.16', 'RouterOS 7.17']),
            'architecture_name' => fake()->randomElement(['arm', 'arm64', 'mipsbe']),
            'platform' => 'MikroTik',
            'uptime' => $isOnline ? fake()->randomElement(['2d 04h', '8d 13h', '21d 09h']) : null,
            'cpu_usage' => $isOnline ? fake()->numberBetween(5, 65) : 0,
            'cpu_count' => fake()->numberBetween(1, 4),
            'cpu_frequency' => fake()->randomElement([650, 716, 800, 864, 1200]),
            'memory_usage' => $isOnline ? fake()->numberBetween(20, 70) : 0,
            'total_memory' => $totalMemory,
            'free_memory' => $freeMemory,
            'total_hdd_space' => $totalHddSpace,
            'free_hdd_space' => $freeHddSpace,
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
