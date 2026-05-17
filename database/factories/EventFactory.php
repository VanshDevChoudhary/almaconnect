<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-3 months', '+6 months');

        return [
            'title' => fake()->unique()->catchPhrase() . ' Meetup',
            'description' => fake()->paragraphs(2, true),
            'cover_image' => null,
            'starts_at' => $start,
            'ends_at' => (clone $start)->modify('+3 hours'),
            'location' => fake()->boolean(60) ? fake('en_IN')->city() . ', India' : null,
            'online_url' => fake()->boolean(40) ? 'https://meet.google.com/' . fake()->bothify('???-????-???') : null,
            'capacity' => fake()->boolean(50) ? fake()->numberBetween(20, 300) : null,
            'created_by' => User::factory(),
        ];
    }

    public function upcoming(): static
    {
        return $this->state(function (array $attributes) {
            $start = fake()->dateTimeBetween('+1 week', '+6 months');

            return [
                'starts_at' => $start,
                'ends_at' => (clone $start)->modify('+3 hours'),
            ];
        });
    }

    public function past(): static
    {
        return $this->state(function (array $attributes) {
            $start = fake()->dateTimeBetween('-1 year', '-1 week');

            return [
                'starts_at' => $start,
                'ends_at' => (clone $start)->modify('+3 hours'),
            ];
        });
    }
}
