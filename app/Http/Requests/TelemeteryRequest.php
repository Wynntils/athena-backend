<?php

namespace App\Http\Requests;

class TelemeteryRequest extends BaseRequest
{
    public function sendGatheringSpot(): array
    {
        return [
            'spot' => 'required|array|min:1',
            'spot.*' => 'required|array',
            'spot.*.type' => 'required|string',
            'spot.*.material' => 'required|string',
            'spot.*.x' => 'required|integer',
            'spot.*.y' => 'required|integer',
            'spot.*.z' => 'required|integer',
        ];
    }
}
