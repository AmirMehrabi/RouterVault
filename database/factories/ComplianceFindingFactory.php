<?php

namespace Database\Factories;

use App\Models\ComplianceFinding;
use App\Models\Router;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplianceFinding>
 */
class ComplianceFindingFactory extends Factory
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
            'rule_key' => fake()->unique()->slug(3),
            'rule_name' => fake()->sentence(3),
            'status' => fake()->randomElement(['pass', 'warning', 'fail']),
            'summary' => fake()->sentence(),
            'remediation' => fake()->optional()->sentence(),
            'checked_at' => now(),
        ];
    }
}
