<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemWeightsCacheResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array{wynnpool: array<string, array<string, array<string, float>>>, nori: array<string, array<string, float>>}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
