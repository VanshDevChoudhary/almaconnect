<?php

namespace App\Http\Requests;

use App\Models\Survey;
use Illuminate\Foundation\Http\FormRequest;

class SurveyResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $survey = $this->route('survey');
        if (! $survey instanceof Survey) {
            $survey = Survey::with('questions')->findOrFail($survey);
        } else {
            $survey->loadMissing('questions');
        }

        $rules = [];
        foreach ($survey->questions as $q) {
            $key = "answers.{$q->id}";
            if ($q->type === 'text') {
                $rules[$key] = ['required', 'string', 'max:1000'];
            } elseif ($q->type === 'single_choice') {
                $rules[$key] = ['required', 'string', 'in:'.implode(',', $q->options ?? [])];
            } else { // multi_choice
                $rules[$key] = ['required', 'array', 'min:1'];
                $rules["{$key}.*"] = ['string', 'in:'.implode(',', $q->options ?? [])];
            }
        }

        return $rules;
    }
}
