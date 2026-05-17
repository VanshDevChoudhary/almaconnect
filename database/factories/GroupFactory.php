<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company() . ' Network',
            'description' => fake()->paragraph(2),
            'cover_image' => null,
            'type' => fake()->randomElement(['regional', 'batch', 'interest', 'professional']),
            'created_by' => User::factory(),
            'is_public' => fake()->boolean(85),
        ];
    }
}
