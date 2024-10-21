<?php

namespace App\Http\Requests\Friend;

use App\Http\Requests\ApiRequest;

class DeleteRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|int|exists:users,id',
        ];
    }
}
