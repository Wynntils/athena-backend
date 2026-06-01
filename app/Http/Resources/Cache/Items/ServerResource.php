<?php

namespace App\Http\Resources\Cache\Items;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /**
             * Unix epoch in milliseconds when this world was first observed online during the current uptime.
             *
             * @var int
             *
             * @example 1780355131362
             */
            'firstSeen' => $this->resource['firstSeen'],
            /**
             * Usernames of players currently on the world.
             *
             * @var list<string>
             *
             * @example ["CuteShinobu","TacoBeanzFR","Bagaskjj"]
             */
            'players' => $this->resource['players'],
        ];
    }
}
