<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $discordInfo = $this->discord_info ?? [];
        $cosmeticInfo = $this->cosmetic_info ?? [];

        return [
            'uuid' => $this->id,
            'username' => $this->username,
            'accountType' => $this->account_type,
            'authToken' => $this->auth_token,
            'versions' => [
                'latest' => $this->latest_version,
                'used' => $this->used_versions ?? [],
            ],
            'discord' => [
                'username' => $discordInfo['username'] ?? null,
                'id' => $discordInfo['id'] ?? null,
            ],
            'cosmetics' => [
                'texture' => $cosmeticInfo['capeTexture'] ?? '',
                'isElytra' => $cosmeticInfo['elytraEnabled'] ?? false,
                'maxResolution' => $cosmeticInfo['maxResolution'] ?? '0x0',
                'allowAnimated' => $cosmeticInfo['allowAnimated'] ?? false,
                'parts' => $cosmeticInfo['parts'] ?? [],
            ],
        ];
    }
}
