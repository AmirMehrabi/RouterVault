<?php

namespace Database\Factories;

use App\Models\DiffAlert;
use App\Models\Router;
use App\Models\RouterBackup;
use App\Models\RouterBackupDiff;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiffAlert>
 */
class DiffAlertFactory extends Factory
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
            'router_backup_id' => RouterBackup::query()->inRandomOrder()->value('id'),
            'previous_router_backup_id' => RouterBackup::query()->inRandomOrder()->value('id'),
            'router_backup_diff_id' => RouterBackupDiff::query()->inRandomOrder()->value('id'),
            'severity' => 'low',
            'status' => 'unread',
            'summary' => 'Router configuration changed',
            'sections' => [],
            'matched_ignored_patterns' => [],
            'added_lines' => 1,
            'removed_lines' => 0,
        ];
    }
}
