<?php

namespace App\Http\Libraries\Requests\Cache;

use App\Models\Guild;

class GuildList implements CacheContract
{

    public function refreshRate(): int
    {
        return 3600;
    }

    public function generate(): array
    {
        return Guild::query()
            ->whereNotNull('id')
            ->get()
            ->map(function ($guild) {
                $arr = $guild->toArray();
                $arr['_id'] = $arr['id'];
                $arr['color'] = isset($arr['color']) ? (string) $arr['color'] : '';

                unset($arr['id']);
                return $arr;
            })
            ->all();
    }

}
