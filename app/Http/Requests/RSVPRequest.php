<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RSVPRequest extends FormRequest
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
            'status' => ['required', 'in:going,interested,not_going'],
        ];
    }
}
