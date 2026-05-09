<?php

namespace Database\Factories;

use App\Models\BackupRun;
use App\Models\BackupSchedule;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BackupRun>
 */
class BackupRunFactory extends Factory
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
            'backup_schedule_id' => BackupSchedule::query()->inRandomOrder()->value('id'),
            'trigger' => 'scheduled',
            'status' => 'queued',
            'total_routers' => 0,
            'successful_backups' => 0,
            'failed_backups' => 0,
        ];
    }
}
