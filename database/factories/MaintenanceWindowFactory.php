<?php

namespace Database\Factories;

use App\Models\MaintenanceWindow;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceWindow>
 */
class MaintenanceWindowFactory extends Factory
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
            'name' => fake()->sentence(3),
            'reason' => fake()->sentence(),
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'scheduled',
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'starts_at' => now()->subMinutes(10),
            'ends_at' => now()->addMinutes(50),
            'status' => 'active',
        ]);
    }
}
