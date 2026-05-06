<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuildListCacheResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<int, array{_id: string, prefix: string, color: string}>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
