<?php

namespace Database\Factories;

use App\Models\DiffAlert;
use App\Models\DiffAlertNote;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiffAlertNote>
 */
class DiffAlertNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'diff_alert_id' => DiffAlert::query()->inRandomOrder()->value('id'),
            'tenant_id' => Tenant::query()->inRandomOrder()->value('id') ?? 'tenant-001',
            'body' => fake()->sentence(),
        ];
    }
}
