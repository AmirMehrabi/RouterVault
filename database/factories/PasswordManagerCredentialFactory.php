<?php

namespace Database\Factories;

use App\Models\PasswordManagerCredential;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PasswordManagerCredential>
 */
class PasswordManagerCredentialFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tenant = Tenant::query()->inRandomOrder()->first();

        return [
            'tenant_id' => $tenant?->id,
            'name' => fake()->randomElement(['Core Router Login', 'Tower AP Admin', 'NOC Shared Device', 'Backhaul Radio']).' '.fake()->unique()->numberBetween(1, 99),
            'username' => fake()->userName(),
            'password' => fake()->password(16, 24),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
