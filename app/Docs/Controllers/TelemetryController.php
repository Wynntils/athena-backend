<?php

namespace App\Docs\Controllers;

use OpenApi\Attributes as OA;

#[
    OA\Post(
        path: "/telemetry/sendGatheringSpot",
        operationId: "sendGatheringSpot",
        summary: "sendGatheringSpot",
        tags: ["Telemetry"],
        responses: [
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
