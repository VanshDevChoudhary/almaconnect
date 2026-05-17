<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreSurveyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'target_audience' => ['required', 'in:all,alumni,students'],
            'is_active' => ['boolean'],
            'questions' => ['required', 'array', 'min:1', 'max:50'],
            'questions.*.question' => ['required', 'string', 'max:500'],
            'questions.*.type' => ['required', 'in:text,single_choice,multi_choice'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*' => ['string', 'max:200'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                foreach ((array) $this->input('questions', []) as $i => $q) {
                    $type = $q['type'] ?? null;
                    if (in_array($type, ['single_choice', 'multi_choice'], true)) {
                        $opts = array_filter((array) ($q['options'] ?? []), fn ($o) => trim((string) $o) !== '');
                        if (count($opts) < 2) {
                            $validator->errors()->add(
                                "questions.{$i}.options",
                                'Choice questions need at least 2 options.'
                            );
                        }
                    }
                }
            },
        ];
    }
}
