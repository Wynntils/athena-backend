<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Top-level keys are leaderboard category identifiers (e.g. `guildLevel`,
 * `combatSoloLevel`, `huntedContent`, `guildSeason31`). For each category the
 * inner map is keyed by rank ("1" through "10") and the value is either a
 * guild name or a player UUID — guild categories return names, player
 * categories return UUIDs.
 */
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
