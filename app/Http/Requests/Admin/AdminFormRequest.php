<?php

namespace App\Http\Requests\Admin;

use App\Support\PlainTextSanitizer;
use Illuminate\Foundation\Http\FormRequest;

abstract class AdminFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') === true;
    }

    public function validated($key = null, $default = null): mixed
    {
        $data = parent::validated($key, $default);

        if (is_string($key)) {
            if (in_array($key, PlainTextSanitizer::SENSITIVE_KEYS, true)) {
                return $data;
            }

            return is_string($data) ? PlainTextSanitizer::cleanString($data) : $data;
        }

        return is_array($data) ? PlainTextSanitizer::clean($data) : $data;
    }
}
