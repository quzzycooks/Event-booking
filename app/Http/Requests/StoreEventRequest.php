<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isOrganizer();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date', 'after:now'],
            'price' => ['required', 'numeric', 'min:0'],
            'total_seats' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'event_date.after' => 'Event date must be in the future.',
            'total_seats.min' => 'Event must have at least 1 seat.',
        ];
    }
}

