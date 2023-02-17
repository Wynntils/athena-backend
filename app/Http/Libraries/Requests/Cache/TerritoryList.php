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
        $wynnTerritories = Http::wynn()->get(config('athena.api.wynn.territories'))->collect('territories');
        if ($wynnTerritories === null) {
            throw new \Exception('Failed to fetch territories from Wynn API');
        }

        return [
            'territories' => $wynnTerritories->map(static function ($item) {
                $guild = Guild::gather($item['guild']);

                $territory = [];
                $territory['territory'] = $item['territory'];
                $territory['guild'] = $item['guild'];
                $territory['guildPrefix'] = $item['guildPrefix'];
                $territory['guildColor'] = empty($guild->color) ? "" : $guild->color;
                $territory['acquired'] = $item['acquired'];
                $territory['attacker'] = ""; // not used
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
            })->toArray()
        ];
    }
}
