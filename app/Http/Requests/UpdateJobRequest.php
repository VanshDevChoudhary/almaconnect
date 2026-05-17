<?php

namespace App\Http\Requests;

class UpdateJobRequest extends StoreJobRequest
{
    public function authorize(): bool
    {
        // Ownership is enforced by JobPolicy in the controller.
        return $this->user() !== null;
    }
}
