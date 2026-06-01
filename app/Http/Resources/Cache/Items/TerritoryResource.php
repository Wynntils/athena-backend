<?php

namespace App\Http\Resources\Cache\Items;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TerritoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $show = collect(explode(',', (string) $request->query('show', '')))
            ->map(fn (string $field) => strtolower(trim($field)))
            ->filter();

        return [
            /**
             * Guild that currently owns the territory.
             *
             * @var array{uuid: string, name: string, prefix: string, hq: string, color: string}
             *
             * @example {"uuid":"0dd24dcc-370c-4e27-a1b4-2dfa92e76667","name":"Aequitas","prefix":"Aeq","hq":"Corrupted Warfront","color":"#ffd700"}
             */
            'guild' => $this->resource['guild'] ?? null,
            /**
             * ISO-8601 timestamp of when the current owner captured the territory.
             *
             * @var string
             *
             * @example "2026-06-01T05:48:10.003000Z"
             */
            'acquired' => $this->resource['acquired'] ?? null,
            /**
             * In-world bounding box of the territory expressed as two corner coordinates.
             *
             * @var array{start: array{int, int}, end: array{int, int}}
             *
             * @example {"start":[-600,-610],"end":[-670,-780]}
             */
            'location' => $this->resource['location'] ?? null,
            /**
             * Whether this territory is the owning guild's headquarters.
             *
             * @var bool
             *
             * @example false
             */
            'hq' => $this->resource['hq'] ?? false,
            /**
             * Per-resource generation/storage state. Only present when `resources` is requested via the `show` query parameter.
             *
             * @var list<array{type: string, generation: int, baseGeneration: int, stored: int, limit: int}>
             *
             * @example [{"type":"EMERALD","generation":9359,"baseGeneration":9000,"stored":31,"limit":3000}]
             */
            'resources' => $this->when(
                $show->contains('resources') && isset($this->resource['resources']),
                fn () => $this->resource['resources'],
            ),
            /**
             * Names of territories this one is directly connected to. Only present when `links` is requested via the `show` query parameter.
             *
             * @var list<string>
             *
             * @example ["Monte's Village","Iboju Village","Troms Lake"]
             */
            'links' => $this->when(
                $show->contains('links') && isset($this->resource['links']),
                fn () => $this->resource['links'],
            ),
            /**
             * Bucketed treasury rating. Only present when `treasury` is requested via the `show` query parameter.
             *
             * @var string
             *
             * @example "LOW"
             */
            'treasury' => $this->when(
                $show->contains('treasury') && isset($this->resource['treasury']),
                fn () => $this->resource['treasury'],
            ),
            /**
             * Bucketed defences rating. Only present when `defences` is requested via the `show` query parameter.
             *
             * @var string
             *
             * @example "HIGH"
             */
            'defences' => $this->when(
                $show->contains('defences') && isset($this->resource['defences']),
                fn () => $this->resource['defences'],
            ),
        ];
    }
}
