<?php

namespace App\Http\Libraries\Requests\Cache;

class Leaderboard implements CacheContract
{

    public function refreshRate(): int
    {
        return 3600;
    }

    public function generate(): array
    {
        // Get data from wynn api
        return [];
    }
}

