<?php

namespace App\Http\Resources\Cache\Items;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuildSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /**
             * The full guild name.
             *
             * @var string
             *
             * @example "Aequitas"
             */
            '_id' => $this->resource['_id'] ?? null,
            /**
             * The short guild prefix / tag.
             *
             * @var string
             *
             * @example "Aeq"
             */
            'prefix' => $this->resource['prefix'] ?? null,
            /**
             * The hex color associated with the guild. May be empty when the guild has no color set.
             *
             * @var string
             *
             * @example "#ffd700"
             */
            'color' => $this->resource['color'] ?? '',
        ];
    }
}
