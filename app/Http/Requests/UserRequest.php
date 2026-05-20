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
            'username' => 'required|string',
        ];
    }

    public function getInfoPost(): array
    {
        return [
            'uuid' => 'required|uuid',
        ];
    }

    public function getInfoV2(): array
    {
        return [
            'uuid' => 'required|uuid',
        ];
    }

    public function uploadCapeWeb(): array
    {
        return [
            'cape' => 'required|file|mimes:png|max:500',
            'name' => 'nullable|string|max:80',
            'visibility' => 'nullable|string|in:public,private',
            'tags' => 'nullable|array|max:10',
            'tags.*' => 'string|max:32',
        ];
    }

    public function selectCape(): array
    {
        return [
            'sha' => ['present', 'nullable', 'regex:/^([0-9a-f]{40})?$/'],
        ];
    }

    public function setElytraMode(): array
    {
        return [
            'elytraEnabled' => 'required|boolean',
        ];
    }
}
