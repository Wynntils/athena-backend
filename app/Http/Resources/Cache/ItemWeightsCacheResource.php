<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemWeightsCacheResource extends JsonResource
{
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        return [
            /**
             * Wynnpool-sourced build weights. Nested as itemName → variant → statId → weight.
             *
             * @var array<string, array<string, array<string, float>>>
             *
             * @example {"Resurgence":{"Combat":{"manaRegen":0.4,"spellDamage":0.1,"healthRegenRaw":0.4,"walkSpeed":0.1}}}
             */
            'wynnpool' => $this->resource['wynnpool'] ?? [],
            /**
             * Nori-sourced build weights. Nested as itemName → variant → statId → weight.
             *
             * @var array<string, array<string, array<string, float>>>
             *
             * @example {"Apocalypse":{"Main":{"lifeSteal":0.75,"exploding":0.105,"healthRegen":0.1}}}
             */
            'nori' => $this->resource['nori'] ?? [],
        ];
    }
}
