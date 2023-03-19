<?php

namespace App\Http\Requests;

class UserRequest extends BaseRequest
{
    public function uploadConfigs(): array
    {
        return [
            'config' => 'required|array|min:1',
            'config.*' => 'required',
        ];
    }

    public function updateDiscord(): array
    {
        return [
            'id' => 'required|int',
            'username' => 'required|string'
        ];
    }

    public function getInfoPost(): array
    {
        return [
            'uuid' => 'required|uuid',
        ];
    }
}
