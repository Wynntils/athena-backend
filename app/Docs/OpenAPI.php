<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[
    OA\OpenApi(
        openapi: "3.0.0",
        info: new OA\Info(
            version: "1.0.0",
            description: "Athena Backend API",
            title: "Athena-Backend",
            license: new OA\License(
                name: "GNU Affero General Public License v3.0",
                url: "https://github.com/Wynntils/Athena/blob/master/LICENSE"
            )
        ),
        servers: [
            new OA\Server(
                url: "http://127.0.0.1:8000",
                description: "Localhost"
            ),
            new OA\Server(
                url: "https://athena.wynntils.com",
                description: "Production"
            )
        ],
        tags: [
            new OA\Tag(name: "Auth"),
            new OA\Tag(name: "Cache"),
            new OA\Tag(name: "Cape"),
            new OA\Tag(name: "Telemetry"),
            new OA\Tag(name: "User"),
        ],
    ),
    OA\SecurityScheme(
        securityScheme: "AuthToken",
        type: "apiKey",
        name: "authToken",
        in: "header"
    ),
    OA\Response(
        response: 'ServerError',
        description: "Internal Server Error",
        content: new OA\JsonContent(properties: [
            new OA\Property(property: "message", description: "Error message", type: "string", example: "Server Error"),
        ])
    ),
    OA\Response(
        response: 'Unauthorized',
        description: "Unauthorized",
        content: new OA\JsonContent(properties: [
            new OA\Property(property: "message", description: "Error message", type: "string", example: "Unauthenticated"),
        ])
    ),
    OA\Response(
        response: 'BadRequest',
        description: "Unprocessable Content",
        content: new OA\JsonContent(properties: [
            new OA\Property(property: "message", description: "Error description", type: "string", example: "The X field is required."),
            new OA\Property(property: "errors", description: "Error message", properties: [
                new OA\Property(property: "X", description: "Error message", type: "array", items: new OA\Items(type: "string", example: "The X field is required.")),
            ], type: "object"),
        ])
    ),
]
class OpenAPI
{
    public const SECURITY_AUTH_TOKEN = [['AuthToken' => []]];
    public const REF_RESPONSE_SERVER_ERROR = '#/components/responses/ServerError';
    public const REF_RESPONSE_UNAUTHORIZED = '#/components/responses/Unauthorized';
    public const REF_RESPONSE_BAD_REQUEST = '#/components/responses/BadRequest';

}
