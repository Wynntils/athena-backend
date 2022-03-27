<?php

namespace App\Http\Requests;

class ApiRequest extends BaseRequest
{
    public function createApiKey(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'adminContact' => 'required|string',
            'maxLimit' => 'required|integer',
        ];
    }

    public function changeApiKey(): array {
        return [
            'key' => 'string|max:255',
            'maxLimit' => 'string',
        ];
    }
}
