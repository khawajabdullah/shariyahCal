<?php

namespace App\Http\Requests\Admin;

use App\Rules\SafePlainText;
use Illuminate\Validation\Rule;

class UpdateScholarRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:180', new SafePlainText],
            'initials' => ['nullable', 'string', 'max:8', new SafePlainText],
            'country' => ['nullable', 'string', 'max:80', new SafePlainText],
            'flag' => ['nullable', 'string', 'max:8', new SafePlainText],
            'bio' => ['nullable', 'string', 'max:5000', new SafePlainText(allowUrls: true)],
            'tier' => ['sometimes', 'string', Rule::in(['standard', 'institutional'])],
            'madhhab_id' => ['nullable', 'integer', 'exists:madhahib,id'],
            'language_ids' => ['sometimes', 'array', 'max:30'],
            'language_ids.*' => ['integer', 'exists:languages,id'],
            'specialties' => ['nullable', 'array', 'max:20'],
            'specialties.*' => ['string', 'max:120', new SafePlainText],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
