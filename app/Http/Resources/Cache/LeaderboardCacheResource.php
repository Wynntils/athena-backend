<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardCacheResource extends JsonResource
{
    public static $wrap = null;

    public bool $preserveKeys = true;

    /**
     * @return array<string, array<string, string>>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
