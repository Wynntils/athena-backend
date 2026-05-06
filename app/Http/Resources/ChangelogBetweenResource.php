<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChangelogBetweenResource extends JsonResource
{
    /**
     * @return array{from: string, to: string, changelogs: array<string, string>}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
