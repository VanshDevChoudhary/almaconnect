<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
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
            'avatar' => ['required', 'image', 'mimes:jpeg,png,webp', 'max:5120'],
        ];
    }

    /**
     * Defend against spoofed extensions by re-checking the real MIME type
     * from the file's actual bytes.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $file = $this->file('avatar');

                if (! $file || ! $file->isValid()) {
                    return;
                }

                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $realMime = finfo_file($finfo, $file->getRealPath());
                finfo_close($finfo);

                if (! in_array($realMime, $allowed, true)) {
                    $validator->errors()->add('avatar', 'The uploaded file is not a valid image.');
                }
            },
        ];
    }
}
