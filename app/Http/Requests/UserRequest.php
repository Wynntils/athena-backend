<?php

namespace App\Http\Requests;

class UserRequest extends BaseRequest
{
    public function uploadConfigs(): array
    {
        return [
            'config' => 'required|file|max:500'
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
            'uuid' => 'required|uuid|exists:App\Models\User,_id',
        ];
    }
}
