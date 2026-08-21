<?php

namespace App\Http\Requests;

use App\Rules\SafePlainText;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scholar_id' => ['required', 'string', 'max:120'],
            'event_type_id' => ['required', 'integer', 'exists:event_types,id'],
            'start' => ['required', 'date'],
            'attendee_name' => ['required', 'string', 'max:180', new SafePlainText],
            'attendee_email' => ['required', 'email', 'max:180'],
            'attendee_phone' => ['nullable', 'string', 'max:40', new SafePlainText],
            'attendee_timezone' => ['nullable', 'timezone'],
            'attendee_language' => ['nullable', 'string', 'max:16'],
            'notes' => ['nullable', 'string', 'max:5000', new SafePlainText],
            'guests' => ['nullable', 'array', 'max:10'],
            'guests.*' => ['email', 'max:180'],
        ];
    }
}
