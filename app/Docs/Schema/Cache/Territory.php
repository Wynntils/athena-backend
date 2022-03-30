<?php

namespace App\Docs\Schema\Cache;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: "Territory",
        properties: [
            new OA\Property(
                property: "territory",
                type: "string",
                example: "Ragni"
            ),
            new OA\Property(
                property: "guild",
                type: "string",
                example: "Blacklisted"
            ),
            new OA\Property(
                property: "guildPrefix",
                type: "string",
                example: "BLA"
            ),
            new OA\Property(
                property: "guildColor",
                type: "string",
                example: "#1e1e1e"
            ),
            new OA\Property(
                property: "acquired",
                type: "string",
                example: "2020-01-01 00:00:00"
            ),
            new OA\Property(
                property: "attacker",
                type: "string",
                nullable: true
            ),
            new OA\Property(
                property: "level",
                type: "number",
                example: 1
            ),
            new OA\Property(
                property: "location",
                ref: "#/components/schemas/TerritoryLocation"
            )
        ]
    )
]
class Territory
{

}
