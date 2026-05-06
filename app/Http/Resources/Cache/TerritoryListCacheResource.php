<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TerritoryListCacheResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, array{
     *     guild: array{name: string|null, prefix: string|null, color: string|null},
     *     acquired: string,
     *     location: array{start: array{int, int}, end: array{int, int}}
     * }>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
