<?php

namespace App\Docs\Schema\Cache\Ingredients;

use OpenApi\Attributes as OA;

#[
    OA\Schema(
        title: "Profession",
        type: "string",
        enum: [
            "WOODCUTTING",
            "MINING",
            "FISHING",
            "FARMING",
            "ALCHEMISM",
            "ARMOURING",
            "COOKING",
            "JEWELING",
            "SCRIBING",
            "TAILORING",
            "WEAPONSMITHING",
            "WOODWORKING",
        ],
    )
]

class Profession
{

}
