<?php

namespace Database\Factories;

use App\Models\Router;
use App\Models\RouterBackup;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RouterBackup>
 */
class RouterBackupFactory extends Factory
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
            'status' => 'success',
            'changed' => false,
            'disk' => 'local',
            'path' => 'router-backups/test.rsc',
            'checksum' => hash('sha256', 'test'),
            'size_bytes' => 5,
        ];
    }
}
