<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class ProfileFactory extends Factory
{
    public function definition(): array
    {
        $batch = fake()->numberBetween(2010, 2024);

        $branch = fake()->randomElement([
            'CSE', 'CSE', 'CSE', 'ECE', 'ECE', 'ME', 'CE', 'EE', 'IT', 'IT', 'Chemical', 'Civil',
        ]);

        $industry = fake()->randomElement([
            'Software', 'Software', 'Software', 'Finance', 'Consulting',
            'Manufacturing', 'Healthcare', 'Education', 'Government', 'Entrepreneurship',
        ]);

        $city = fake()->randomElement([
            'Bangalore', 'Bangalore', 'Bangalore', 'Bangalore', 'Bangalore',
            'Delhi', 'Delhi', 'Delhi',
            'Mumbai', 'Mumbai', 'Mumbai',
            'Hyderabad', 'Hyderabad',
            'Chennai', 'Chennai',
            'Pune', 'Pune',
            'Kolkata', 'Ahmedabad', 'Jaipur',
        ]);

        $country = fake()->randomElement([
            'India', 'India', 'India', 'India', 'India', 'India', 'India', 'India', 'India', 'India',
            'India', 'India', 'India', 'India', 'India',
            'USA', 'USA', 'USA',
            'UK',
            'Germany',
        ]);

        $startupNames = ['Hyperloop Labs', 'NimbusAI', 'Zentra', 'Kraftbase', 'Flowstack', 'Quanta Systems', 'Bytecrew'];
        $company = fake()->boolean(30)
            ? fake()->randomElement($startupNames)
            : fake()->randomElement([
                'Google', 'Microsoft', 'Amazon', 'Infosys', 'TCS', 'Wipro',
                'Razorpay', 'Zomato', 'Swiggy', 'Flipkart', 'Freshworks', 'Goldman Sachs', 'Deloitte',
            ]);

        if ($batch <= 2015) {
            $role = fake()->randomElement(['Senior Engineer', 'Engineering Manager', 'Director', 'VP Engineering', 'Principal Engineer']);
        } elseif ($batch <= 2020) {
            $role = fake()->randomElement(['Software Engineer II', 'Senior Analyst', 'Product Manager', 'Tech Lead']);
        } else {
            $role = fake()->randomElement(['Associate Engineer', 'Junior Developer', 'Analyst', 'Graduate Trainee']);
        }

        $skillPool = [
            'Python', 'JavaScript', 'Java', 'React', 'Node.js', 'Go', 'AWS', 'Docker',
            'Kubernetes', 'Machine Learning', 'SQL', 'TypeScript', 'GraphQL', 'Rust',
            'System Design', 'Data Engineering', 'Terraform', 'Kafka',
        ];
        $skills = fake()->randomElements($skillPool, fake()->numberBetween(3, 8));

        $name = fake('en_IN')->name();

        return [
            'user_id' => User::factory(),
            'batch' => $batch,
            'branch' => $branch,
            'roll_no' => 'R' . fake()->numberBetween(10000, 99999),
            'current_company' => $company,
            'current_role' => $role,
            'industry' => $industry,
            'city' => $city,
            'country' => $country,
            'bio' => fake()->paragraph(3),
            'skills' => $skills,
            'linkedin_url' => 'https://linkedin.com/in/' . \Illuminate\Support\Str::slug($name) . '-' . fake()->numberBetween(100, 999),
            'website_url' => fake()->boolean(25) ? fake()->url() : null,
        ];
    }
}
