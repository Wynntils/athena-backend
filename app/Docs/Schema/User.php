<?php

namespace App\Docs\Schema;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: "User",
        properties: [
            new OA\Property(
                property: "accountType",
                type: "enum",
                enum: [
                    "NORMAL",
                    "BANNED",
                    "DONATOR",
                    "CONTENT_TEAM",
                    "HELPER",
                    "MODERATOR",
                ],
            ),
            new OA\Property(
                property: "cosmetics",
                properties: [
                    new OA\Property(
                        property: "hasCape",
                        type: "bool",
                    ),
                    new OA\Property(
                        property: "hasElytra",
                        type: "bool",
                    ),
                    new OA\Property(
                        property: "hasEars",
                        type: "bool",
                    ),
                    new OA\Property(
                        property: "texture",
                        type: "string",
                    ),
                ],
                type: "object"
            ),
        ]
    )
]
class User
{

}
