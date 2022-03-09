<?php

namespace App\Http\Libraries\Requests;

class WynnRequest
{
    public static function request(): \Illuminate\Http\Client\PendingRequest
    {
        return \Http::withHeaders(['apiKey' => config('athena.api.wynn.apiKey')])
            ->withUserAgent(config('athena.general.userAgent'))
            ->connectTimeout(50)
            ->timeout(50);
    }
}
