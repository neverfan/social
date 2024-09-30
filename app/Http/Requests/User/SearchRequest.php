<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiRequest;

class SearchRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'limit' => 'integer|max:1000',
            'page' => 'integer|max:1000',
        ];
    }
}
