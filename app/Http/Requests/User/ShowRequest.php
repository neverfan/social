<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiRequest;

class ShowRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['user_id' => $this->route('user_id')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|int',
        ];
    }
}
