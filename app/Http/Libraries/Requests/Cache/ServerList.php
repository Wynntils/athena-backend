<?php

namespace App\Http\Libraries\Requests\Cache;

class ServerList implements CacheContract
{

    public function refreshRate(): int
    {
        return 30;
    }

    public function generate(): array
    {
        // Get data from wynn api
        return [];
    }
}

