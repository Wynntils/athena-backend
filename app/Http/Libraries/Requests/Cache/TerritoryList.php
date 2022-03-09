<?php

namespace App\Http\Libraries\Requests\Cache;

use App\Http\Libraries\Requests\WynnRequest;
use App\Models\Guild;

class TerritoryList implements CacheContract
{

    public function refreshRate(): int
    {
        return 30;
    }

    public function generate(): array
    {
        $wynnTerritories = WynnRequest::request()->get(config('athena.api.wynn.territories'))->collect('territories');
        if ($wynnTerritories === null) {
            return [];
        }

        return $wynnTerritories->map(static function ($item) {
            $guild = Guild::gather($item['guild']);

            $territory = [];
            $territory['territory'] = $item['territory'];
            $territory['guild'] = $guild->id;
            $territory['guildPrefix'] = $guild->prefix;
            $territory['guildColor'] = $guild->color;
            $territory['acquired'] = $item['acquired'];
            $territory['attacker'] = $item['attacker'];
            $territory['level'] = 1; // not used

            if (array_key_exists('location', $item)) {
                $location = [];
                $location['startX'] = $item['location']['startX'];
                $location['startZ'] = $item['location']['startY'];
                $location['endX'] = $item['location']['endX'];
                $location['endZ'] = $item['location']['endY'];
                $territory['location'] = $location;
            }

            return $territory;
        })->toArray();
    }
}

