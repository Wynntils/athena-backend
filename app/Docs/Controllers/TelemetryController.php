<?php

namespace App\Docs\Controllers;

use App\Docs\OpenAPI;
use App\Http\Enums\GatheringMaterial;
use App\Http\Enums\ProfessionType;
use OpenApi\Attributes as OA;

#[
    OA\Post(
        path: "/telemetry/sendGatheringSpot",
        operationId: "sendGatheringSpot",
        summary: "sendGatheringSpot",
        security: OpenAPI::SECURITY_AUTH_TOKEN,
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["spot"],
                properties: [
                    new OA\Property(
                        property: "spot",
                        properties: [
                            new OA\Property(property: "type", type: "enum", enum: [
                                ProfessionType::WOODCUTTING,
                                ProfessionType::MINING,
                                ProfessionType::FARMING,
                                ProfessionType::FISHING,
                            ]),
                            new OA\Property(property: "material", type: "enum", enum: [
                                GatheringMaterial::OAK,
                                GatheringMaterial::BIRCH,
                                GatheringMaterial::WILLOW,
                                GatheringMaterial::ACACIA,
                                GatheringMaterial::SPRUCE,
                                GatheringMaterial::JUNGLE,
                                GatheringMaterial::DARK,
                                GatheringMaterial::LIGHT,
                                GatheringMaterial::PINE,
                                GatheringMaterial::AVO,
                                GatheringMaterial::SKY,
                                GatheringMaterial::COPPER,
                                GatheringMaterial::GRANITE,
                                GatheringMaterial::GOLD,
                                GatheringMaterial::SANDSTONE,
                                GatheringMaterial::IRON,
                                GatheringMaterial::SILVER,
                                GatheringMaterial::COBALT,
                                GatheringMaterial::KANDERSTONE,
                                GatheringMaterial::DIAMOND,
                                GatheringMaterial::MOLTEN,
                                GatheringMaterial::VOIDSTONE,
                                GatheringMaterial::WHEAT,
                                GatheringMaterial::BARLEY,
                                GatheringMaterial::OATS,
                                GatheringMaterial::MALT,
                                GatheringMaterial::HOPS,
                                GatheringMaterial::RYE,
                                GatheringMaterial::MILLET,
                                GatheringMaterial::DECAY_ROOTS,
                                GatheringMaterial::RICE,
                                GatheringMaterial::SORGHUM,
                                GatheringMaterial::HEMP,
                                GatheringMaterial::GUDGEON,
                                GatheringMaterial::TROUT,
                                GatheringMaterial::SALMON,
                                GatheringMaterial::CARP,
                                GatheringMaterial::ICEFISH,
                                GatheringMaterial::PIRANHA,
                                GatheringMaterial::KOI,
                                GatheringMaterial::GYLIA_FISH,
                                GatheringMaterial::BASS,
                                GatheringMaterial::MOLTEN_EEL,
                                GatheringMaterial::STARFISH,
                                GatheringMaterial::DERNIC,
                            ]),
                            new OA\Property(property: "x", type: "integer"),
                            new OA\Property(property: "y", type: "integer"),
                            new OA\Property(property: "z", type: "integer"),
                        ],
                        type: "object"
                    ),
                ]
            )
        ),
        tags: ["Telemetry"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Success",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "message", type: "string", example: "Gathering spot saved"),
                    ]
                )
            ),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_BAD_REQUEST, response: 422),
            new OA\Response(ref: OpenAPI::REF_RESPONSE_SERVER_ERROR, response: 500)
        ]
    ),
]
class TelemetryController
{
}
