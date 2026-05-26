<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LootPoolsCacheResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
