<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use App\Models\WirelessClient;
use App\Models\WirelessClientManagementLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WirelessClientManagementLog>
 */
class WirelessClientManagementLogFactory extends Factory
{
    public function definition(): array
    {
        $wirelessClient = WirelessClient::query()->inRandomOrder()->first();
        $tenant = $wirelessClient?->tenant ?? Tenant::query()->inRandomOrder()->first();
        $user = User::query()->when($tenant, fn ($query) => $query->where('tenant_id', $tenant->id))->inRandomOrder()->first();

        return [
            'tenant_id' => $tenant?->id,
            'wireless_client_id' => $wirelessClient?->id,
            'user_id' => $user?->id,
            'action_key' => fake()->randomElement(['discovery', 'set_identity', 'set_dns', 'reboot']),
            'action_label' => fake()->sentence(2),
            'status' => fake()->randomElement(['success', 'failed']),
            'target_host' => fake()->ipv4(),
            'request_payload' => ['source' => 'factory'],
            'command_batch' => ['/system/identity/print'],
            'response_payload' => ['ok' => true],
            'summary' => fake()->sentence(),
            'error_message' => null,
            'started_at' => now()->subMinutes(2),
            'finished_at' => now()->subMinute(),
        ];
    }
}
