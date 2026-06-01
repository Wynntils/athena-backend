<?php

namespace App\Http\Resources\Cache;

use App\Http\Resources\Cache\Items\TerritoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TerritoryListCacheResource extends JsonResource
{
    /**
     * Territories keyed by territory name. Use the `show` query parameter
     * (comma-separated `links`, `treasury`, `defences`, `resources`) to opt
     * into the heavier per-territory fields.
     *
     * @return array<string, TerritoryResource>
     */
    public function toArray(Request $request): array
    {
        $out = [];

        foreach ($this->resource as $name => $territory) {
            if (! is_array($territory)) {
                continue;
            }

            $out[$name] = (new TerritoryResource($territory))->resolve($request);
        }

        return $out;
    }
}
