<?php

namespace App\Http\Requests;

use App\Http\Enums\GatheringMaterial;
use App\Http\Enums\ProfessionType;

class TelemetryRequest extends BaseRequest
{
    public function saveGatheringSpot(): array
    {;
        return [
            'spot' => 'required|array',
            'spot.type' => 'required|string|in:'.collect(ProfessionType::cases())->pluck('value')->implode(','),
            'spot.material' => 'required|string|in:'.collect(GatheringMaterial::cases())->pluck('value')->implode(','),
            'spot.x' => 'required|integer',
            'spot.y' => 'required|integer',
            'spot.z' => 'required|integer',
        ];
    }
}
