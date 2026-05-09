<?php

namespace Database\Factories;

use App\Models\BackupSchedule;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BackupSchedule>
 */
class BackupScheduleFactory extends Factory
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
            'name' => fake()->words(2, true).' backups',
            'is_enabled' => true,
            'interval_value' => 1,
            'interval_unit' => 'days',
            'timezone' => 'UTC',
            'retention_count' => 30,
            'export_mode' => 'full',
            'next_run_at' => now(),
        ];
    }
}
