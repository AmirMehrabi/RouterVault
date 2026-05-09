<?php

namespace Database\Factories;

use App\Models\RouterBackup;
use App\Models\RouterBackupDiff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RouterBackupDiff>
 */
class RouterBackupDiffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'router_backup_id' => RouterBackup::query()->inRandomOrder()->value('id'),
            'previous_router_backup_id' => RouterBackup::query()->inRandomOrder()->value('id'),
            'added_lines' => 1,
            'removed_lines' => 0,
            'unified_diff' => "@@ -1,1 +1,1 @@\n+/system identity set name=test",
            'hunks' => [],
        ];
    }
}
