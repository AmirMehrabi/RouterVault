<?php

namespace Database\Factories;

use App\Models\Badge;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserBadge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserBadge>
 */
class UserBadgeFactory extends Factory
{
    public function definition(): array
    {
        $user = User::query()->inRandomOrder()->first() ?? User::factory()->create();
        $tenantId = $user->tenant_id ?? Tenant::query()->value('id');

        return [
            'tenant_id' => $tenantId,
            'user_id' => $user->id,
            'badge_id' => Badge::query()->inRandomOrder()->value('id') ?? Badge::factory()->create()->id,
            'awarded_at' => now(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
