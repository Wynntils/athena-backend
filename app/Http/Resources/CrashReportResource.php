<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CrashReportResource extends JsonResource
{
    /**
     * @return array{message: string, hash: string}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
