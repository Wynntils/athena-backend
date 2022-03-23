<?php

namespace App\Http\Libraries\Requests\Cache;

use App\Http\Libraries\Requests\WynnRequest;

class MapLocations implements CacheContract
{

    public function refreshRate(): int
    {
        return 86400;
    }

    public function generate(): array
    {
        $wynnMapLocations = WynnRequest::request()->get(config('athena.api.wynn.mapLocations'))->collect();
        if ($wynnMapLocations === null) {
            return [];
        }
        $wynnMapLocations->forget('request');

        $wynnMapLabels = WynnRequest::request()->get(config('athena.api.wynn.mapLabels'))->collect();
        if ($wynnMapLabels === null) {
            return [];
        }

        $wynnMapLocations['labels'] = $wynnMapLabels['labels'];

        $npcLocations = WynnRequest::request()->get(config('athena.api.wynn.npcLocations'))->collect();
        if ($npcLocations === null) {
            return [];
        }

        $wynnMapLocations['npc-locations'] = $npcLocations['npc-locations'];

        return $wynnMapLocations->toArray();
    }
}

