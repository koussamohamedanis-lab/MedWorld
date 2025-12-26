<?php

namespace App\Http\Requests;

class RegisterRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone_number' => 'nullable|string',
            'address' => 'nullable|string',
            'gender' => 'nullable|in:male,female',
            'date_of_birth' => 'nullable|date',
            'type' => 'required|in:superadmin,admin,doctor,assistant,patient',
        ];
    }
}
