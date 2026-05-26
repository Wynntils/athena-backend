<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TerritoryListCacheResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function toArray(Request $request): array
    {
        $show = collect(explode(',', (string) $request->query('show', '')))
            ->map(fn (string $field) => strtolower(trim($field)))
            ->filter();

        $includeLinks = $show->contains('links');
        $includeTreasury = $show->contains('treasury');
        $includeDefences = $show->contains('defences');
        $includeResources = $show->contains('resources');

        return collect($this->resource)
            ->map(function ($territory) use ($includeLinks, $includeTreasury, $includeDefences, $includeResources) {
                if (! is_array($territory)) {
                    return $territory;
                }

                if (! $includeLinks) {
                    unset($territory['links']);
                }

                if (! $includeTreasury) {
                    unset($territory['treasury']);
                }

                if (! $includeDefences) {
                    unset($territory['defences']);
                }

                if (! $includeResources) {
                    unset($territory['resources']);
                }

                return $territory;
            })
            ->toArray();
    }
}
