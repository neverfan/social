<?php

namespace App\Http\Requests\Post;

use App\Http\Requests\ApiRequest;

class FeedRequest extends ApiRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'offset' => 'integer|nullable',
            'limit' => 'integer|nullable',
        ];
    }
}
