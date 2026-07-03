<?php

namespace Database\Factories;

use App\Models\ConfigurationBaseline;
use App\Models\Router;
use App\Models\RouterBackup;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConfigurationBaseline>
 */
class ConfigurationBaselineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::query()->inRandomOrder()->value('id') ?? 'tenant-001',
            'router_id' => Router::query()->inRandomOrder()->value('id'),
            'router_backup_id' => RouterBackup::query()->inRandomOrder()->value('id'),
            'label' => 'Approved baseline',
            'notes' => fake()->optional()->sentence(),
            'approved_at' => now(),
        ];
    }
}
