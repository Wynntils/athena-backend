<?php

namespace App\Http\Resources\Cache;

use App\Http\Resources\Cache\Items\ServerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServerListCacheResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $servers = [];

        foreach ($this->resource['servers'] ?? [] as $id => $server) {
            $servers[$id] = (new ServerResource($server))->resolve($request);
        }

        return [
            /**
             * Active Wynncraft worlds, keyed by world ID (e.g. `WC1`, `NA22`, `EU9`).
             *
             * @var array<string, ServerResource>
             */
            'servers' => $servers,
        ];
    }
}
