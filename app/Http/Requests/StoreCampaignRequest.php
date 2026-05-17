<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
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
            'description' => ['required', 'string', 'max:10000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
            'target_amount' => ['nullable', 'integer', 'min:1000'],
            'ends_at' => ['nullable', 'date', 'after:today'],
            'is_active' => ['boolean'],
        ];
    }
}
