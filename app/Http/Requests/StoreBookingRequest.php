<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAttendee();
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.min' => 'You must book at least 1 ticket.',
        ];
    }
}

