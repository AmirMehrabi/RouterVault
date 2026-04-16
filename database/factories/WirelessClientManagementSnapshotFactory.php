<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\WirelessClient;
use App\Models\WirelessClientManagementSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WirelessClientManagementSnapshot>
 */
class WirelessClientManagementSnapshotFactory extends Factory
{
    public function definition(): array
    {
        $wirelessClient = WirelessClient::query()->inRandomOrder()->first();
        $tenant = $wirelessClient?->tenant ?? Tenant::query()->inRandomOrder()->first();

        return [
            'tenant_id' => $tenant?->id,
            'wireless_client_id' => $wirelessClient?->id,
            'action_key' => fake()->randomElement(['discovery', 'refresh_signal', 'refresh_dhcp_lease']),
            'snapshot_type' => fake()->randomElement(['discovery', 'signal', 'dhcp_lease', 'configuration']),
            'payload' => ['source' => 'factory'],
            'collected_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
        ];
    }
}
