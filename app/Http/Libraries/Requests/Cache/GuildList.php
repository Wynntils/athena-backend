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
        return Guild::where('_id', '!=', null)->get()->toArray();
    }
}
