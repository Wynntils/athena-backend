<?php

namespace App\Http\Libraries\Requests\Cache;


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

        return [
            'territories' => $wynnTerritories->mapWithKeys(static function ($item, $key) {
                $item['territory'] = $key; // v3 api
                $guild = Guild::gather($item);

                $territory = [];
                $territory['territory'] = $item['territory'];
                $territory['guild'] = $item['guild']['name'];
                $territory['guildPrefix'] = $item['guild']['prefix'];
                $territory['guildColor'] = empty($guild->color) ? generateColorAndUpdate($guild) : $guild->color;
                $territory['acquired'] = $item['acquired'];

                if (array_key_exists('location', $item)) {
                    $location = [];
                    $location['startX'] = $item['location']['start'][0];
                    $location['startZ'] = $item['location']['start'][1];
                    $location['endX'] = $item['location']['end'][0];
                    $location['endZ'] = $item['location']['end'][1];
                    $territory['location'] = $location;
                }

                return [$key => $territory];
            })->toArray()
        ];
    }
}
