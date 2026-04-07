<?php

namespace Database\Factories;

use App\Models\AccessPoint;
use App\Models\AccessPointStatusChange;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AccessPointStatusChange>
 */
class AccessPointStatusChangeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tenant = Tenant::query()->inRandomOrder()->first();
        $accessPoint = AccessPoint::query()
            ->when($tenant, fn ($query) => $query->where('tenant_id', $tenant->id))
            ->inRandomOrder()
            ->first();
        $previousStatus = fake()->randomElement(['online', 'offline', 'maintenance']);
        $currentStatus = collect(['online', 'offline', 'maintenance'])
            ->reject(fn (string $status): bool => $status === $previousStatus)
            ->random();

        return [
            'tenant_id' => $tenant?->id ?? $accessPoint?->tenant_id,
            'access_point_id' => $accessPoint?->id,
            'previous_status' => $previousStatus,
            'current_status' => $currentStatus,
            'reason' => fake()->optional()->sentence(),
            'checked_at' => now()->subMinutes(fake()->numberBetween(1, 720)),
            'meta' => [
                'clients_count' => fake()->numberBetween(0, 50),
            ],
        ];
    }
}
