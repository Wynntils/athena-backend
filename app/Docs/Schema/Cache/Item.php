<?php

namespace App\Docs\Schema\Cache;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: "Item",
        properties: [
            new OA\Property(
                property: "displayName",
                type: "string"
            ),
            new OA\Property(
                property: "tier",
                ref: "#/components/schemas/Tier",
            ),
            new OA\Property(
                property: "identified",
                type: "boolean"
            ),
            new OA\Property(
                property: "powderAmount",
                type: "integer"
            ),
            new OA\Property(
                property: "attackSpeed",
                ref: "#/components/schemas/AttackSpeed",
            ),
            new OA\Property(
                property: "itemInfo",
                ref: "#/components/schemas/ItemInfo",
                type: "object"
            ),
            new OA\Property(
                property: "requirements",
                ref: "#/components/schemas/Requirements",
                type: "object"
            ),
            new OA\Property(
                property: "damageTypes",
                ref: "#/components/schemas/DamageTypes",
                type: "object"
            ),
            new OA\Property(
                property: "defenseTypes",
                ref: "#/components/schemas/DefenseTypes",
                type: "object"
            ),
            new OA\Property(
                property: "statuses",
                ref: "#/components/schemas/Statuses",
                type: "object"
            ),
            new OA\Property(
                property: "lore",
                type: "string"
            ),
            new OA\Property(
                property: "wynnBuilderID",
                type: "integer"
            ),
        ],
        type: "object"
    ),
]
class Item
{

}
