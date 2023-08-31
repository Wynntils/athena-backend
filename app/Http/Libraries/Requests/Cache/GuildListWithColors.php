<?php

namespace App\Http\Libraries\Requests\Cache;

use App\Models\Guild;

class GuildListWithColors implements CacheContract
{

    public function refreshRate(): int
    {
        return 3600;
    }

    public function generate(): array
    {
        return Guild::all()->filter(function($guild) {
            return $guild->color !== null && $guild->color !== '';
        })->toArray();
    }
}
