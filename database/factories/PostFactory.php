<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(fake()->numberBetween(1, 4)),
            'image' => null,
            'is_pinned' => fake()->boolean(10),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
