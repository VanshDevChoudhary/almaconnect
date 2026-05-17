<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255'],
            'batch' => ['nullable', 'integer', 'min:1980', 'max:'.(date('Y') + 5)],
            'branch' => ['nullable', 'string', 'max:100', 'in:CSE,ECE,ME,CE,EE,IT,Chemical,Civil,Other'],
            'roll_no' => ['nullable', 'string', 'max:50'],
            'current_company' => ['nullable', 'string', 'max:255'],
            'current_role' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'skills' => ['nullable', 'array', 'max:20'],
            'skills.*' => ['string', 'max:50'],
            'linkedin_url' => ['nullable', 'url', 'max:500', 'regex:/linkedin\.com/i'],
            'website_url' => ['nullable', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'linkedin_url.regex' => 'Enter a valid LinkedIn URL (must contain linkedin.com).',
        ];
    }
}
