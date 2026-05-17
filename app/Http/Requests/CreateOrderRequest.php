<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
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
            'campaign_id' => ['nullable', 'integer', 'exists:donation_campaigns,id'],
            'amount' => ['required', 'integer', 'min:100', 'max:1000000'],
            'is_anonymous' => ['boolean'],
        ];
    }
}
