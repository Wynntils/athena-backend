<?php

namespace App\Docs\Controllers;

use OpenApi\Attributes as OA;

#[
    OA\Post(
        path: "/telemetry/sendGatheringSpot",
        operationId: "sendGatheringSpot",
        summary: "sendGatheringSpot",
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ["spot"],
                properties: [
                    new OA\Property(
                        property: "spot",
                        properties: [
                            new OA\Property(property: "type", type: "string"),
                            new OA\Property(property: "material", type: "string"),
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
            new OA\Response(
                ref: "#/components/responses/ServerError",
                response: 500
            )
        ]
    ),
]
class TelemetryController
{
}
