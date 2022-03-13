<?php

namespace App\Http\Libraries\Requests\Cache;

use App\Http\Libraries\Requests\WynnRequest;

class ServerList implements CacheContract
{

    public function refreshRate(): int
    {
        return 30;
    }

    public function generate(): array
    {
        $wynnOnlinePlayers = WynnRequest::request()->get(config('athena.api.wynn.onlinePlayers'))->collect()->forget('request');
        if ($wynnOnlinePlayers === null) {
            return [];
        }

        $result = [];

        $validServers = [];
        foreach ($wynnOnlinePlayers as $key => $onlinePlayer) {
            $server = [];

            $validServers[] = $key;

            $server['firstSeen'] = \Cache::rememberForever($key, static function () {
                return currentTimeMillis();
            });
            $server['players'] = $onlinePlayer;

            $result['servers'][$key] = $server;
        }

//        TODO: Run Through Valid Servers, and Remove "Old" Servers

        return $result;
    }
}

