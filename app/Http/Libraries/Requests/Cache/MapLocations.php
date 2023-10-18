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
        /** @var \Illuminate\Http\Client\Response[] $responses */
        $responses = Http::wynn()->pool(fn (Pool $pool) => [
            $pool->as('wynnMapLocations')->get(config('athena.api.wynn.v3.mapLocations')),
            $pool->as('wynnMapLabels')->get(config('athena.api.wynn.mapLabels')),
            $pool->as('npcLocations')->get(config('athena.api.wynn.npcLocations')),
        ]);

        $response = [];

        $wynnMapLocations = $responses['wynnMapLocations']->json();
        if ($wynnMapLocations === null) {
            throw new \Exception('Failed to fetch map locations from Wynn API');
        }
        $response['locations'] = collect($wynnMapLocations)->filter(function ($location) {
            return $location['icon'] !== "Content_Raid.png" && $location['icon'] !== "Special_SeaskipperFastTravel.png" && $location['icon'] !== "Special_HousingAirBalloon.png";
        })->values();

        $wynnMapLabels = $responses['wynnMapLabels']->collect();
        if ($wynnMapLabels === null) {
            throw new \Exception('Failed to fetch map labels from Data Storage');
        }

        $response['labels'] = $wynnMapLabels['labels'];

        $npcLocations = $responses['npcLocations']->collect();
        if ($npcLocations === null) {
            throw new \Exception('Failed to fetch NPC locations from Data Storage');
        }

        $response['npc-locations'] = $npcLocations['npc-locations'];

        return $response;
    }
}
