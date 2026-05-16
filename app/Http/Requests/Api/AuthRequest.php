<?php

namespace App\Http\Requests\Api;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class AuthRequest extends FormRequest
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
        $rules = [];

        // Rules for Register
        if ($this->isMethod('post') && $this->routeIs('api.register')) {
            $rules = [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ];
        }

        // Rules for Login
        if ($this->isMethod('post') && $this->routeIs('api.login')) {
            $rules = [
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ];
        }

        // Example for Update Profile (Edit)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules = [
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.auth()->id()],
                'phone' => ['nullable', 'string', 'max:20'],
            ];
        }

        return $rules;
    }
}
