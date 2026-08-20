<?php

namespace App\Http\Requests\Admin;

use App\Rules\SafePlainText;
use Illuminate\Validation\Rule;

class UpdateLanguageRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge(['code' => strtolower(trim((string) $this->input('code')))]);
        }
    }

    public function rules(): array
    {
        $language = $this->route('language');

        return [
            'name' => ['required', 'string', 'max:80', new SafePlainText],
            'code' => ['required', 'string', 'max:12', 'alpha_dash', Rule::unique('languages', 'code')->ignore($language), new SafePlainText],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
