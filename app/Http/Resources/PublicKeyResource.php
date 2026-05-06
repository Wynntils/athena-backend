<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicKeyResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array{publicKeyIn: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'publicKeyIn' => $this->resource,
        ];
    }
}
