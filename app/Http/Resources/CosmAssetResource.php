<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CosmeticAsset */
class CosmAssetResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        $upCount = $this->votes->where('vote', 1)->count();
        $downCount = $this->votes->where('vote', -1)->count();

        $authUser = $request->user();
        $isUploader = $authUser && $authUser->id === $this->uploader_id;

        return [
            'sha' => $this->sha,
            'type' => $this->type->value,
            'slot' => $this->slot->value,
            'status' => $this->status->value,
            'name' => $this->name,
            'visibility' => $this->visibility->value,
            'tags' => $this->tags ?? [],
            'width' => $this->width,
            'height' => $this->height,
            'animated' => $this->isAnimated(),
            'equip_count' => $this->equip_count,
            'uploaded_at' => $this->uploaded_at?->toISOString(),
            'uploader' => $this->uploader ? [
                'id' => $this->uploader->id,
                'username' => $this->uploader->username,
            ] : null,
            'votes' => [
                'up' => $upCount,
                'down' => $downCount,
            ],
            'has_pending_edit' => $isUploader ? $this->hasPendingEdit() : null,
        ];
    }
}
