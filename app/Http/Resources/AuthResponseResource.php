<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResponseResource extends JsonResource
{
    /**
     * @return array{message: string, authToken: string, configFiles: array<string, mixed>, hashes: array<string, string|null>}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
