<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServerListCacheResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array{servers: array<string, array{firstSeen: int, players: string[]}>}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
