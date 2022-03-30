<?php

namespace App\Http\Requests;

class AuthRequest extends BaseRequest
{
    public function responseEncryption(): array
    {
        return [
            'username' => 'required',
            'key' => 'required',
            'version' => 'required',
        ];
    }
}
