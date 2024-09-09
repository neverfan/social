<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiRequest;

class RegisterRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'password' => 'required|min:6',
            'first_name' => 'required|min:3',
            'last_name' => 'required|min:3',
            'gender' => 'required|in:male,female',
            'city' => 'required|min:2',
            'birth_date' => 'required|date',
            'biography' => 'required|min:10',
        ];
    }
}
