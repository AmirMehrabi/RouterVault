<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Badge>
 */
class BadgeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(['manual', 'automatic']),
            'icon_path' => null,
            'display' => fake()->randomElement(['username', 'profile']),
            'rule_class' => null,
        ];
    }
}
