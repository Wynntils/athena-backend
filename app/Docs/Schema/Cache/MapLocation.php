<?php

namespace App\Docs\Schema\Cache;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: 'MapLocation',
        properties: [
            new OA\Property(
                property: 'name',
                description: "The name of the location.",
                type: "string"
            ),
            new OA\Property(
                property: 'icon',
                description: "The icon of the location.",
                type: "string"
            ),
            new OA\Property(
                property: 'x',
                description: "The x coordinate of the location.",
                type: "integer"
            ),
            new OA\Property(
                property: 'y',
                description: "The y coordinate of the location.",
                type: "integer"
            ),
            new OA\Property(
                property: 'z',
                description: "The z coordinate of the location.",
                type: "integer"
            ),
        ],
        type: "object"
    )
]
class MapLocation
{

}
