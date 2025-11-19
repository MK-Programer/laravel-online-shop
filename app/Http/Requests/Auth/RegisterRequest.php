<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** password regex
         * (?=.*[A-Za-z]) → must contain at least one letter
         * (?=.*\d) → must contain at least one number
         * (?=.*[@$!%*#?&]) → must contain at least one special character
         */
        return [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|phone:US,EG|unique:users',
            'password' => 'required|min:5|regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).+$/|confirmed'
        ];
    }

    public function messages(): array
    {
        return [
            'phone.phone' => 'Please enter a valid phone number (US or Egypt).',
            'password.regex' => 'Password must contain letters, numbers, and special characters.',
        ];
    }
}
