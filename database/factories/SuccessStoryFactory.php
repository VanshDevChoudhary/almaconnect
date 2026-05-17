<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SuccessStory>
 */
class SuccessStoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'headline' => fake()->unique()->catchPhrase(),
            'body' => fake()->paragraphs(5, true),
            'cover_image' => null,
            'category' => fake()->randomElement(['entrepreneurship', 'research', 'social_impact', 'career', 'other']),
            'status' => 'pending',
            'submitted_by' => User::factory(),
            'reviewed_by' => null,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'reviewed_by' => null,
            'published_at' => null,
        ]);
    }
}
