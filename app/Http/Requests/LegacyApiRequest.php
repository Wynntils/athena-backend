<?php

namespace App\Http\Requests;

use App\Http\Enums\AccountType;

class LegacyApiRequest extends BaseRequest
{
    public function getUserData(): array
    {
        return [
            'user' => 'required|string',
        ];
    }
    public function setAccountType(): array
    {
        return [
            'user' => 'required|string',
            'type' => 'required|string|in:'.collect(AccountType::cases())->pluck('value')->implode(','),
        ];
    }
    public function updateCosmetics(): array
    {
        return [
            'user' => 'required|string',
            'cosmetics' => 'required|array',
            'cosmetics.parts' => 'array',
            'cosmetics.parts.ears' => 'boolean',
        ];
    }
    public function setGuildColor(): array
    {
        return [
            'guild' => 'required|string',
            'color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/|max:7|min:7|unique:guilds,color',
        ];
    }
    public function setUserPassword(): array
    {
        return [
            'token' => 'required|uuid|exists:App\Models\User,authToken',
            'password' => 'required|string',
        ];
    }
    public function getUserByPassword(): array
    {
        return [
            'user' => 'required|string',
            'password' => 'required|string',
        ];
    }
    public function getUserConfig(): array
    {
        return [
            'user' => 'required|string',
            'configName' => 'required|string',
        ];
    }
}
