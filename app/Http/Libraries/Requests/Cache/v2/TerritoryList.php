<?php

namespace App\Http\Libraries\Requests\Cache\v2;


use App\Http\Libraries\Requests\Cache\CacheContract;
use App\Models\Guild;
use Http;
use Illuminate\Support\Facades\Cache;

class TerritoryList implements CacheContract
{

    public function refreshRate(): int
    {
        return 15;
    }

    public function generate(): array
    {
        $wynnTerritories = Http::wynn()
            ->get(config('athena.api.wynn.v3.territories'))
            ->collect();

        if ($wynnTerritories === null) {
            throw new \Exception('Failed to fetch territories from Wynn API');
        }

        return $wynnTerritories->map(function ($t) {
            $prefix = data_get($t, 'guild.prefix');
            $name   = data_get($t, 'guild.name');
            if ($name || $prefix) {
                $id  = $prefix ? 'p:' . mb_strtoupper($prefix) : 'n:' . mb_strtolower($name);
                $key = 'guild_color:' . sha1($id);

                $color = Cache::remember($key, 3600, function () use ($t) {
                    $guild = Guild::gather(['guild' => data_get($t, 'guild')]);
                    return $guild->color ?: generateColorAndUpdate($guild);
                });

                data_set($t, 'guild.color', $color);
            }
            return $t;
        })->toArray();
    }
}
