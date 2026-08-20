<?php

namespace App\Http\Requests\Admin;

use App\Rules\SafePlainText;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateMadhhabRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    public function rules(): array
    {
        $madhhab = $this->route('madhhab');

        return [
            'name' => ['required', 'string', 'max:120', new SafePlainText],
            'slug' => ['required', 'string', 'max:140', 'alpha_dash', Rule::unique('madhahib', 'slug')->ignore($madhhab), new SafePlainText],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
