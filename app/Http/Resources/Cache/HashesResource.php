<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HashesResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array{guildList: string|null, serverList: string|null, itemWeights: string|null, leaderboard: string|null, territoryList: string|null}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
