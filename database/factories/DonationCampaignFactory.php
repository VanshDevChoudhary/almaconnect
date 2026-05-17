<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DonationCampaign>
 */
class DonationCampaignFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->randomElement([
                'New Library Wing Fund',
                'Scholarship for Underprivileged Students',
                'Robotics Lab Upgrade',
                'Sports Complex Renovation',
                'Alumni Mentorship Endowment',
                'Campus Green Initiative',
            ]),
            'description' => fake()->paragraphs(3, true),
            'cover_image' => null,
            'target_amount' => fake()->numberBetween(5, 50) * 100000,
            'raised_amount' => 0,
            'ends_at' => fake()->dateTimeBetween('+1 month', '+1 year')->format('Y-m-d'),
            'is_active' => true,
        ];
    }
}
