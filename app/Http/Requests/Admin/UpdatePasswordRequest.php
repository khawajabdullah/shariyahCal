<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'string', 'confirmed', 'different:current_password', Password::min(8)->letters()->numbers()],
            'password_confirmation' => ['required', 'string'],
        ];
    }
}
