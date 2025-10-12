<?php

namespace App\Http\Libraries\Requests\Cache\v2;


use App\Http\Libraries\Requests\Cache\CacheContract;
use App\Models\Guild;
use Http;

class TerritoryList implements CacheContract
{

    public function refreshRate(): int
    {
        return 30;
    }

    public function generate(): array
    {
        $wynnTerritories = Http::wynn()->get(config('athena.api.wynn.v3.territories'))->collect();
        if ($wynnTerritories === null) {
            throw new \Exception('Failed to fetch territories from Wynn API');
        }

        return $wynnTerritories->map(function ($t) {
            if (!empty($t['guild']) && !empty($t['guild']['name'])) {
                $guild = Guild::gather(['guild' => $t['guild']]);
                $t['guild']['color'] = empty($guild->color)
                    ? generateColorAndUpdate($guild)
                    : $guild->color;
            }
            return $t;
        })->toArray();
    }
}
