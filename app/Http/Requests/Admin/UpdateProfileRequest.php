<?php

namespace App\Http\Requests\Admin;

use App\Rules\SafePlainText;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', new SafePlainText],
            'email' => [
                'required',
                'email:filter',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()?->id),
                new SafePlainText,
            ],
        ];
    }
}
