<?php

namespace Database\Factories;

use App\Models\ChangeRequest;
use App\Models\Router;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChangeRequest>
 */
class ChangeRequestFactory extends Factory
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
            'status' => 'draft',
            'title' => fake()->sentence(4),
            'reason' => fake()->paragraph(),
            'ticket_reference' => fake()->optional()->bothify('NOC-####'),
            'implementation_plan' => fake()->paragraph(),
        ];
    }
}
