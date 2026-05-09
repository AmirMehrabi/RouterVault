<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'slug' => 'verified',
                'name' => 'Verified',
                'type' => 'manual',
                'display' => 'username',
                'description' => 'Verified user badge, shown next to username.',
                'rule_class' => null,
            ],
            [
                'slug' => 'early_adopter_100',
                'name' => 'Early Adopter',
                'type' => 'automatic',
                'display' => 'profile',
                'description' => 'Automatically granted to first 100 users.',
                'rule_class' => 'EarlyAdopterRule',
            ],
            [
                'slug' => 'first_post',
                'name' => 'First Post',
                'type' => 'automatic',
                'display' => 'profile',
                'description' => 'Awarded when a user creates their first post.',
                'rule_class' => 'FirstPostRule',
            ],
            [
                'slug' => 'first_comment',
                'name' => 'First Comment',
                'type' => 'automatic',
                'display' => 'profile',
                'description' => 'Awarded when a user creates their first comment.',
                'rule_class' => 'FirstCommentRule',
            ],
            [
                'slug' => 'popular_post',
                'name' => 'Popular Post',
                'type' => 'automatic',
                'display' => 'profile',
                'description' => 'Awarded when a post gets a threshold number of likes within 24 hours.',
                'rule_class' => 'PopularPostRule',
            ],
            [
                'slug' => 'streak_7',
                'name' => '7-Day Streak',
                'type' => 'automatic',
                'display' => 'profile',
                'description' => 'User has logged in or interacted for 7 consecutive days.',
                'rule_class' => 'Streak7Rule',
            ],
        ];

        foreach ($badges as $badge) {
            Badge::query()->updateOrCreate(
                ['slug' => $badge['slug']],
                $badge
            );
        }
    }
}
