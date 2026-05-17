<?php

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SurveyQuestion>
 */
class SurveyQuestionFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['text', 'single_choice', 'multi_choice']);

        return [
            'survey_id' => Survey::factory(),
            'question' => fake()->sentence(8) . '?',
            'type' => $type,
            'options' => $type === 'text'
                ? null
                : fake()->randomElements(
                    ['Strongly agree', 'Agree', 'Neutral', 'Disagree', 'Strongly disagree', 'Maybe', 'Not sure'],
                    fake()->numberBetween(3, 5)
                ),
            'order' => fake()->numberBetween(0, 10),
        ];
    }
}
