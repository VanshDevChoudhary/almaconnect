<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    public function definition(): array
    {
        $min = fake()->numberBetween(6, 30) * 100000;

        return [
            'posted_by' => User::factory(),
            'title' => fake()->jobTitle(),
            'company' => fake()->randomElement([
                'Google', 'Microsoft', 'Razorpay', 'Zomato', 'Swiggy', 'Flipkart',
                'Freshworks', 'Infosys', 'TCS', 'CRED', 'PhonePe', 'Meesho',
            ]),
            'location' => fake()->randomElement(['Bangalore', 'Hyderabad', 'Pune', 'Remote', 'Gurgaon', 'Mumbai']),
            'type' => fake()->randomElement(['full_time', 'internship', 'contract', 'part_time']),
            'description' => fake()->paragraphs(3, true),
            'skills' => fake()->randomElements(
                ['Python', 'Java', 'React', 'AWS', 'Node.js', 'Go', 'SQL', 'Docker', 'Kubernetes', 'TypeScript'],
                fake()->numberBetween(3, 6)
            ),
            'salary_min' => $min,
            'salary_max' => $min + fake()->numberBetween(2, 15) * 100000,
            'salary_currency' => 'INR',
            'apply_url' => fake()->boolean(60) ? fake()->url() : null,
            'apply_email' => fake()->boolean(50) ? fake()->companyEmail() : null,
            'status' => 'active',
            'expires_at' => fake()->dateTimeBetween('+2 weeks', '+3 months'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'expires_at' => fake()->dateTimeBetween('+2 weeks', '+3 months'),
        ]);
    }

    public function filled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'filled',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => fake()->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }
}
