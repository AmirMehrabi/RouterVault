<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Incident>
 */
class IncidentFactory extends Factory
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
            'severity' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => 'detected',
            'summary' => fake()->sentence(),
            'impact' => fake()->optional()->sentence(),
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'resolved',
            'acknowledged_at' => now()->subMinutes(10),
            'resolved_at' => now(),
            'resolution' => fake()->sentence(),
        ]);
    }
}
