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
            throw new \Exception('Failed to fetch map locations from Wynn API');
        }
        $wynnMapLocations->forget('request');
        $wynnMapLocations['locations'] = collect($wynnMapLocations['locations'])->filter(function ($location) {
            return $location['icon'] !== "Content_Raid.png" && $location['icon'] !== "Special_SeaskipperFastTravel.png" && $location['icon'] !== "Special_HousingAirBalloon.png";
        })->values()->toArray();

        $wynnMapLabels = $responses['wynnMapLabels']->collect();
        if ($wynnMapLabels === null) {
            throw new \Exception('Failed to fetch map labels from Data Storage');
        }

        $wynnMapLocations['labels'] = $wynnMapLabels['labels'];

        $npcLocations = $responses['npcLocations']->collect();
        if ($npcLocations === null) {
            throw new \Exception('Failed to fetch NPC locations from Data Storage');
        }

        $wynnMapLocations['npc-locations'] = $npcLocations['npc-locations'];

        return $wynnMapLocations->toArray();
    }
}
