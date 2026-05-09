<?php

namespace Database\Factories;

use App\Models\DiffAlertSetting;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiffAlertSetting>
 */
class DiffAlertSettingFactory extends Factory
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
            'is_enabled' => true,
            'ignore_blank_lines' => true,
            'ignored_sections' => [],
            'ignored_keywords' => [],
        ];
    }
}
