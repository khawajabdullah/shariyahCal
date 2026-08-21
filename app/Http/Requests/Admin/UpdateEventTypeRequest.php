<?php

namespace App\Http\Requests\Admin;

class UpdateEventTypeRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'currency' => ['sometimes', 'string', 'size:3'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('currency') && is_string($this->input('currency'))) {
            $this->merge([
                'currency' => mb_strtolower(trim($this->input('currency'))),
            ]);
        }
    }
}
