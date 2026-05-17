<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RosterEntry>
 */
class RosterEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake('en_IN')->name(),
            'email' => fake()->boolean(70) ? fake()->unique()->safeEmail() : null,
            'batch' => fake()->numberBetween(2010, 2024),
            'branch' => fake()->randomElement(['CSE', 'ECE', 'ME', 'CE', 'EE', 'IT', 'Chemical', 'Civil']),
            'roll_no' => 'R' . fake()->numberBetween(10000, 99999),
        ];
    }
}
