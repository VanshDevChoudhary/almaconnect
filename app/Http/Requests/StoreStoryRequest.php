<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStoryRequest extends FormRequest
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
            'headline' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:entrepreneurship,research,social_impact,career,other'],
            'cover_image' => [$this->isMethod('post') ? 'required' : 'nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'body' => ['required', 'string', 'min:500', 'max:50000'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'in:pending,published,rejected'],
        ];
    }
}
