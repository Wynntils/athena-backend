<?php

namespace App\Http\Resources\Cache\Items;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorldEventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /**
             * Display name of the world event.
             *
             * @var string
             *
             * @example "Haywire Defender"
             */
            'name' => $this->resource['name'],
            /**
             * Stable internal identifier used by the game client.
             *
             * @var string
             *
             * @example "822c3aab"
             */
            'internalName' => $this->resource['internalName'],
            /**
             * Flavor text shown to the player when the event triggers.
             *
             * @var string
             *
             * @example "What instructions the old constructs were given have been long forgotten, and now they attack all that come across their path with indiscriminate hatred."
             */
            'lore' => $this->resource['lore'],
            /**
             * Event difficulty rating.
             *
             * @var string
             *
             * @example "MEDIUM"
             */
            'difficulty' => $this->resource['difficulty'],
            /**
             * Recommended combat level for the event.
             *
             * @var int
             *
             * @example 3
             */
            'level' => $this->resource['level'],
            /**
             * Expected duration bucket of the event.
             *
             * @var string
             *
             * @example "SHORT"
             */
            'length' => $this->resource['length'],
            /**
             * Rewards earned at each completion tier. Outer index is the tier, inner list is the rewards granted at that tier.
             *
             * @var list<list<string>>
             *
             * @example [["Various Items and Ingredients","+Exclusive Item","+Decrepit Sewers Key"],["+Infested Pit Key"]]
             */
            'rewardPerLevel' => $this->resource['rewardPerLevel'],
            /**
             * Player-side requirements needed to participate in the event.
             *
             * @var list<array{type: string, value: int}>
             *
             * @example [{"type":"COMBAT_LEVEL","value":3}]
             */
            'requirements' => $this->resource['requirements'],
            /**
             * Possible locations where the event can spawn, each with event/spawn/reward coordinates and detection radii.
             *
             * @var list<array{event: array{x: int, y: int, z: int}, spawn: array{x: int, y: int, z: int}, reward: array{x: int, y: int, z: int}, radius: int, spawnRadius: int}>
             */
            'location' => $this->resource['location'],
            /**
             * ISO-8601 timestamp of the next scheduled occurrence, or null when not currently scheduled.
             *
             * @var string|null
             *
             * @example "2026-06-01T23:31:00+00:00"
             */
            'schedule' => $this->resource['schedule'] ?? null,
        ];
    }
}
