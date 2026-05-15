<?php

namespace App\Http\Requests;

class WebLoginRequest extends BaseRequest
{
    public function login(): array
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string',
            'remember' => 'boolean',
        ];
    }
}
