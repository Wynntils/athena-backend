<?php

namespace App\Docs\Schema\Cache;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: "GatheringSpot",
        description: "A gathering spot is a place where you can gather resources.",
        properties: [
            new OA\Property(
                property: "lastSeen",
                description: "The id of the gathering spot.",
                type: "number",
                example: 1648397703,
            ),
            new OA\Property(
                property: "type",
                description: "The type of the gathering spot."
            ),
            new OA\Property(
                property: "relibility",
                description: "The relibility of the gathering spot.",
                type: "integer"
            ),
            new OA\Property(
                property: "location",
                ref: "#/components/schemas/Location",
                description: "The location of the gathering spot."
            ),
        ]
    ),
]
class GatheringSpot
{
}
