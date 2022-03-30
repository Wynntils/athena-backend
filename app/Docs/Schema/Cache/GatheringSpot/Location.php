<?php

namespace App\Docs\Schema\Cache\GatheringSpot;

use OpenApi\Attributes as OA;

#[
    OA\Schema
    (
        title: "Location",
        properties: [
            new OA\Property(property: "x", type: "integer"),
            new OA\Property(property: "y", type: "integer"),
            new OA\Property(property: "z", type: "integer"),
        ],
        type: "object"
    )
]
class Location
{
}
