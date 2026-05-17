<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['alumni', 'admin'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:full_time,internship,contract,part_time'],
            'description' => ['required', 'string', 'max:10000'],
            'skills' => ['nullable', 'array', 'max:15'],
            'skills.*' => ['string', 'max:50'],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0', 'gte:salary_min'],
            'salary_currency' => ['nullable', 'in:INR,USD,EUR,GBP'],
            'apply_url' => ['nullable', 'url', 'max:500', 'required_without:apply_email'],
            'apply_email' => ['nullable', 'email', 'max:255', 'required_without:apply_url'],
            'expires_at' => ['required', 'date', 'after:today', 'before:'.now()->addDays(91)->toDateString()],
        ];
    }

    public function messages(): array
    {
        return [
            'apply_url.required_without' => 'Provide an apply URL or an apply email.',
            'apply_email.required_without' => 'Provide an apply URL or an apply email.',
            'salary_max.gte' => 'Maximum salary must be greater than or equal to the minimum.',
            'expires_at.before' => 'Expiry cannot be more than 90 days from now.',
        ];
    }
}
