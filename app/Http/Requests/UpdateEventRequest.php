<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isOrganizer();
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'location' => ['sometimes', 'string', 'max:255'],
            'event_date' => ['sometimes', 'date', 'after:now'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'total_seats' => ['sometimes', 'integer', 'min:1'],
            'status' => ['sometimes', 'in:draft,published,cancelled'],
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

