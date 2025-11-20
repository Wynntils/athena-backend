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
                $guild = Guild::gather(['guild' => $item['guild'] ?? null]);

                $guildName = data_get($item, 'guild.name');
                $guildPrefix = data_get($item, 'guild.prefix', 'NONE');

                if ($guildName === null && is_string($item['guild'] ?? null)) {
                    $guildName = $item['guild'];
                }

                $territory = [];
                $territory['territory'] = $key;
                $territory['guild'] = $guildName;
                $territory['guildPrefix'] = $guildPrefix;
                $territory['guildColor'] = empty($guild->color) ? generateColorAndUpdate($guild) : $guild->color;
                $territory['acquired'] = $item['acquired'];

                $start = data_get($item, 'location.start');
                $end = data_get($item, 'location.end');
                if (is_array($start) && is_array($end)) {
                    $location = [];
                    $location['startX'] = $start[0] ?? null;
                    $location['startZ'] = $start[1] ?? null;
                    $location['endX'] = $end[0] ?? null;
                    $location['endZ'] = $end[1] ?? null;
                    $territory['location'] = $location;
                }

                return [$key => $territory];
            })->toArray()
        ];
    }
}
