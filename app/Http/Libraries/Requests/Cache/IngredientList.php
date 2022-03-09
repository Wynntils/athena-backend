<?php

namespace App\Http\Libraries\Requests\Cache;

class IngredientList implements CacheContract
{

    public function refreshRate(): int
    {
        return 86400;
    }

    public function generate(): array
    {
        // Get data from wynn api
        return [];
    }
}

