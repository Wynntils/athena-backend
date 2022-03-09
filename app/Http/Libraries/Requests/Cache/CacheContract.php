<?php

namespace App\Http\Libraries\Requests\Cache;

interface CacheContract
{
    public function refreshRate(): int;

    public function generate(): array;
}
