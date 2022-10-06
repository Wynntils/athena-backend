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
        return [
            'uuid' => $this->id,
            'accountType' => $this->accountType,
            'authToken' => $this->authToken,
            'versions' => [
                'latest' => $this->latestVersion,
                'used' => $this->usedVersions,
            ],
            'discord' => [
                'username' => $this->discordInfo->username,
                'id' => $this->discordInfo->id,
            ],
            'cosmetics' => [
                'texture' => $this->cosmeticInfo?->capeTexture ?? '',
                'isElytra' => $this->cosmeticInfo?->elytraEnabled ?? false,
                'maxResolution' => $this->cosmeticInfo?->maxResolution ?? '0x0',
                'allowAnimated' => $this->cosmeticInfo?->allowAnimated ?? false,
                'parts' => $this->cosmeticInfo?->parts ?? [],
            ],
        ];
    }
}
