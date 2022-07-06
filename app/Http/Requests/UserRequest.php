<?php

namespace App\Http\Requests;

class UserRequest extends BaseRequest
{
    public function uploadConfigs(): array
    {
        return [
            'config' => 'required|array|min:1',
            'config.*' => 'required|file|max:5000',
        ];
    }

    public function updateDiscord(): array
    {
        return [
            'id' => 'required|int',
            'username' => 'required|string'
        ];
    }

    public function getInfoLegacy(): array
    {
        return [
            'uuid' => 'required|uuid',
        ];
    }
}
