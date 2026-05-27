<?php

namespace App\Http\Libraries\Requests\Cache;

use Http;

class WorldEvents
{
    /**
     * @throws \Exception
     */
    public function generate(): array
    {
        $response = Http::wynn()
            ->get(config('athena.api.wynn.v3.worldEvents'));

        if (! $response->successful()) {
            throw new \UnexpectedValueException('Failed to fetch world events from Wynn API.');
        }

        $json = $response->json();
        if (! is_array($json) || array_key_exists('error', $json)) {
            $error = data_get($json, 'detail');
            throw new \UnexpectedValueException('Failed to fetch world events from Wynn API: '.$error);
        }

        return $json;
    }
}
