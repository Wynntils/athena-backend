<?php

namespace App\Http\Requests;

class GuildRequest extends BaseRequest
{
    public function setColor(): array
    {
        return [
            'guild' => 'required|string|exists:guilds,id',
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
