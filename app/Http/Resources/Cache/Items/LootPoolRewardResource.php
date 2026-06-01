<?php

namespace App\Http\Resources\Cache\Items;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LootPoolRewardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /**
             * Display name of the reward.
             *
             * @var string
             *
             * @example "Stardew"
             */
            'name' => $this->resource['name'],
            /**
             * Reward category (e.g. `ITEM`, `WARD`, `CURRENCY`, `TOME`).
             *
             * @var string
             *
             * @example "ITEM"
             */
            'type' => $this->resource['type'],
            /**
             * Quantity of the reward granted in a single roll.
             *
             * @var int
             *
             * @example 1
             */
            'amount' => $this->resource['amount'],
            /**
             * Whether this reward is guaranteed in the pool's drop table.
             *
             * @var bool
             *
             * @example false
             */
            'always' => $this->resource['always'],
            /**
             * Rarity tier for itemized rewards. Omitted on non-itemized rewards.
             *
             * @var string
             *
             * @example "MYTHIC"
             */
            'tier' => $this->when(array_key_exists('tier', $this->resource), fn () => $this->resource['tier']),
            /**
             * Whether the reward rolls as a shiny variant. Omitted when the reward cannot be shiny.
             *
             * @var bool
             *
             * @example true
             */
            'shiny' => $this->when(array_key_exists('shiny', $this->resource), fn () => $this->resource['shiny']),
        ];
    }
}
