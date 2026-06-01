<?php

namespace App\Http\Resources\Cache\Items;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LootPoolResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            /**
             * Display name of the pool's source (camp or raid).
             *
             * @example "Canyon of the Lost Excursion (South)"
             */
            'name' => $this->resource['name'],
            /**
             * Stable internal identifier for the loot source.
             *
             * @example "SCotlCamp1"
             */
            'internalName' => $this->resource['internalName'],
            /**
             * Source category — `CAMP` or `RAID`.
             *
             * @example "CAMP"
             */
            'type' => $this->resource['type'],
            /**
             * Possible rewards that may roll from the pool.
             *
             * @type LootPoolRewardResource[]
             */
            'rewards' => LootPoolRewardResource::collection($this->resource['rewards']),
        ];
    }
}
