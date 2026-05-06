<?php

namespace App\Http\Resources;

use App\Http\Libraries\CapeManager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    public static $wrap = null;
    /**
     * @return array{user: array{accountType: string, cosmetics: array{hasCape: bool, hasElytra: bool, hasEars: bool, texture: string}}}
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'accountType' => $this->account_type->value,
                'cosmetics'   => [
                    'hasCape'   => $this->hasCape(),
                    'hasElytra' => $this->hasElytra(),
                    'hasEars'   => $this->hasPart('ears'),
                    'texture'   => CapeManager::instance()->getCapeAsBase64($this->getFormattedTexture(), true),
                ],
            ],
        ];
    }
}
