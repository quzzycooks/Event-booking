<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'max:100'],
            'last_name' => ['required', 'max:100'],
            'role' => ['required', 'in:organizer,attendee'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed']
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email already in use'
        ];
    }
}
