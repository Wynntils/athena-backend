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

    /**
     * @throws \Exception
     */
    public function generate(): array
    {
        $response = Http::wynn()
            ->get(config('athena.api.wynn.v3.territories'));

        if(!$response->successful()) {
            throw new \Exception('Failed to fetch territories from Wynn API.');
        }

        $json = $response->json();
        if(!is_array($json) || array_key_exists('error', $json)) {
            $error = data_get($json, 'detail');
            throw new \Exception('Failed to fetch territories from Wynn API: ' . $error);
        }

        $wynnTerritories = collect($json);

        return $wynnTerritories->map(function ($t) {
            $rawGuild = data_get($t, 'guild');

            // Safeguard against unexpected structures (string guild names, missing keys, etc.)
            if (is_string($rawGuild)) {
                $rawGuild = ['name' => $rawGuild, 'prefix' => data_get($t, 'guildPrefix')];
                data_set($t, 'guild', $rawGuild);
            }

            $prefix = data_get($rawGuild, 'prefix');
            $name   = data_get($rawGuild, 'name');
            if ($name || $prefix) {
                $guild = Guild::gather(['guild' => $rawGuild]);

                $id  = $prefix ? 'p:' . mb_strtoupper($prefix) : 'n:' . mb_strtolower($name);
                $key = 'guild_color:' . hash('sha512', $id);

                $color = Cache::remember($key, 3600, function () use ($guild) {
                    return $guild->color ?: generateColorAndUpdate($guild);
                });

                data_set($t, 'guild.color', $color);
            }
            return $t;
        })->toArray();
    }
}
