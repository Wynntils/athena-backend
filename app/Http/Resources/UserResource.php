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
            'username' => $this->username,
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
                "texture" => $this->cosmeticInfo->capeTexture,
                "isElytra" => $this->cosmeticInfo->elytraEnabled,
                "maxResolution" => $this->cosmeticInfo->maxResolution,
                "allowAnimated" => $this->cosmeticInfo->allowAnimated,
                'parts' => $this->cosmeticInfo->parts,
            ],
        ];
    }
}
