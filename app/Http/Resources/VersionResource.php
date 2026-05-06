<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VersionResource extends JsonResource
{
    /**
     * @return array{version: string, url: string, md5: string|null, changelog: string, supportedMcVersion?: string}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
