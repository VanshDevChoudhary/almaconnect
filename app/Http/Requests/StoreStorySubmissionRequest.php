<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStorySubmissionRequest extends FormRequest
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
            'headline' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:entrepreneurship,research,social_impact,career,other'],
            'cover_image' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'body' => ['required', 'string', 'min:500', 'max:50000'],
        ];
    }
}
