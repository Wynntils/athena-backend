<?php

namespace App\Http\Libraries\Requests\Cache;


use Http;
use Illuminate\Http\Client\Pool;

class MapLocations implements CacheContract
{

    public function refreshRate(): int
    {
        return 86400;
    }

    public function generate(): array
    {
        $responses = Http::wynn()->pool(fn (Pool $pool) => [
            $pool->as('wynnMapLocations')->get(config('athena.api.wynn.mapLocations')),
            $pool->as('wynnMapLabels')->get(config('athena.api.wynn.mapLabels')),
            $pool->as('npcLocations')->get(config('athena.api.wynn.npcLocations')),
        ]);


        $wynnMapLocations = $responses['wynnMapLocations']->collect();
        if ($wynnMapLocations === null) {
            return [];
        }
        $wynnMapLocations->forget('request');
        $wynnMapLocations['locations'] = collect($wynnMapLocations['locations'])->filter(function ($location) {
            return $location['icon'] !== "Content_Raid.png";
        })->values()->toArray();

        $wynnMapLabels = $responses['wynnMapLabels']->collect();
        if ($wynnMapLabels === null) {
            return [];
        }

        $wynnMapLocations['labels'] = $wynnMapLabels['labels'];

        $npcLocations = $responses['npcLocations']->collect();
        if ($npcLocations === null) {
            return [];
        }

        $wynnMapLocations['npc-locations'] = $npcLocations['npc-locations'];

        return $wynnMapLocations->toArray();
    }
}

