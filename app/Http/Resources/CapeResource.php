<?php

namespace App\Http\Resources;

use App\Models\Cape;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Cape */
class CapeResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'sha' => $this->_id,
            'uploadedBy' => $this->uploadedBy,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'users' => User::where('cosmeticInfo.capeTexture', $this->_id)->get(['_id', 'username', 'accountType']),
        ];
    }
}
