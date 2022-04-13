<?php

namespace App\Docs\Controllers;

use App\Docs\OpenAPI;
use OpenApi\Attributes as OA;

#[
    OA\Get(
        path: "/cache/get/gatheringSpots",
        operationId: "gatheringSpots",
        summary: "Get Gathering Spots",
        tags: ["Cache"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully Returned Gathering Spots",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "woodCutting", type: "array",
                        items: new OA\Items(ref: "#/components/schemas/GatheringSpot", type: "object")
                    ),
                    new OA\Property(property: "mining", type: "array",
                        items: new OA\Items(ref: "#/components/schemas/GatheringSpot", type: "object")
                    ),
                    new OA\Property(property: "farming", type: "array",
                        items: new OA\Items(ref: "#/components/schemas/GatheringSpot", type: "object")
                    ),
                    new OA\Property(property: "fishing", type: "array",
                        items: new OA\Items(ref: "#/components/schemas/GatheringSpot", type: "object")
                    ),
                ])
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500),
        ]
    ),
    OA\Get(
        path: "/cache/get/ingredientList",
        operationId: "ingredientList",
        summary: "Get ingredient list",
        tags: ["Cache"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully Returned Ingredient List",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "ingredients", description: "Ingredients List", type: "array",
                        items: new OA\Items(ref: "#/components/schemas/Ingredients", type: "object")
                    ),
                ])
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500),
        ]
    ),
    OA\Get(
        path: "/cache/get/itemList",
        operationId: "itemList",
        summary: "Get Item List",
        tags: ["Cache"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully Returned Item List",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "items", description: "Items", type: "array",
                        items: new OA\Items(ref: "#/components/schemas/Item", type: "object")
                    ),
                ])
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500),
        ]
    ),
    OA\Get(
        path: "/cache/get/leaderboard",
        operationId: "leaderboard",
        summary: "Get Leaderboard",
        tags: ["Cache"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully Returned Leaderboard",
                content: new OA\JsonContent(
                    type: "object",
                    example: [
                        "879be29a-bcca-43d6-978a-321a4241c392" => [
                            "name" => "Scyu_",
                            "timePlayed" => 21510,
                            "ranks" => [
                                "WOODCUTTING" => 1,
                                "FISHING" => 1,
                                "FARMING" => 1,
                            ],
                        ]
                    ],
                    additionalProperties: new OA\AdditionalProperties(
                        ref: "#/components/schemas/Leaderboard",
                    ),
                )
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500),
        ]
    ),
    OA\Get(
        path: "/cache/get/mapLocations",
        operationId: "mapLocations",
        summary: "Get Map Locations",
        tags: ["Cache"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully Returned Map Locations",
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: "locations", type: "array",
                        items: new OA\Items(ref: "#/components/schemas/MapLocation", type: "object")
                    ),
                ])
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500),
        ]
    ),
    OA\Get(
        path: "/cache/get/serverList",
        operationId: "serverList",
        summary: "Get Server List",
        tags: ["Cache"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully Returned Server List",
                content: new OA\JsonContent(
                    type: "object",
                    example: [
                        "WC1" => [
                            "firstSeen" => 1647620343300,
                            "players" => [
                                "Maarcus",
                                "LonelyJJ",
                                "Aiza",
                                "_Eao_",
                                "LazyShock",
                                "myrinni",
                                "Whagard",
                                "adridid03",
                                "Berded",
                                "Hatezola",
                                "Freddie_Hg_39",
                                "snowxq",
                                "SnogleHD",
                                "famulusmortris",
                                "R1M8TKlNEU70f5",
                                "WoodCreature",
                                "Kawarama_senju",
                                "Depresssive",
                                "VIIP_ER",
                                "samylo",
                                "XavyLo",
                                "DogeTennant",
                                "TheSurvivor0",
                                "legoman123",
                                "HopFeet",
                                "Jwnk",
                                "xTofuuu",
                                "BubsE",
                                "Vexzyyy",
                                "Lord_Clucky",
                                "Irony",
                                "Henry03MC",
                                "RoSeNur"
                            ],
                        ]
                    ],
                    additionalProperties: new OA\AdditionalProperties(
                        ref: "#/components/schemas/Server",
                    )
                )
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500),
        ]
    ),
    OA\Get(
        path: "/cache/get/territoryList",
        operationId: "territoryList",
        summary: "Get Territory List",
        tags: ["Cache"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully Returned Territory List",
                content: new OA\JsonContent(properties: [
                    new OA\Property(
                        property: "territories",
                        description: "Territory Data",
                        type: "object",
                        additionalProperties: new OA\AdditionalProperties(
                            ref: "#/components/schemas/Territory",
                            type: "object",
                        )
                    ),
                ])
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500),
        ]
    ),
    OA\Get(
        path: "/cache/getHashes",
        operationId: "getHashes",
        summary: "Get Hashes",
        tags: ["Cache"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Successfully Returned Hashes",
                content: new OA\JsonContent(ref: "#/components/schemas/Hashes", type: "object")
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500),
        ]
    )
]
class CacheController
{

}
