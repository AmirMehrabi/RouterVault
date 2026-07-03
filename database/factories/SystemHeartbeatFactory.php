<?php

namespace Database\Factories;

use App\Models\SystemHeartbeat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SystemHeartbeat>
 */
class SystemHeartbeatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service' => fake()->unique()->slug(2),
            'node' => 'default',
            'status' => 'healthy',
            'metadata' => [],
            'last_seen_at' => now(),
        ];
    }

    public function stale(): static
    {
        return $this->state(fn (array $attributes): array => [
            'last_seen_at' => now()->subHour(),
        ]);
    }
}
