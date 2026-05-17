<?php

namespace Database\Factories;

use App\Models\DonationCampaign;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donation>
 */
class DonationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'campaign_id' => DonationCampaign::factory(),
            'amount' => fake()->numberBetween(5, 500) * 100,
            'currency' => 'INR',
            'razorpay_order_id' => 'order_' . Str::upper(Str::random(14)),
            'razorpay_payment_id' => null,
            'razorpay_signature' => null,
            'status' => 'pending',
            'is_anonymous' => fake()->boolean(15),
            'receipt_path' => null,
        ];
    }

    public function success(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'success',
            'razorpay_payment_id' => 'pay_' . Str::upper(Str::random(14)),
            'razorpay_signature' => Str::random(64),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
