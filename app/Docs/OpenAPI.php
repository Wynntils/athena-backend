<?php

namespace App\Docs;

use OpenApi\Attributes as OA;

#[
    OA\OpenApi(
        openapi: "3.0.0",
        info: new OA\Info(
            version: "Dev-Master",
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
        securityScheme: "ApiKey",
        type: "apiKey",
        name: "apiKey",
        in: "header"
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
]
class OpenAPI
{
    public const SECURITY_API_KEY = [['ApiKey' => []]];
    public const SECURITY_AUTH_TOKEN = [['AuthToken' => []]];
    public const REF_RESPONSE_SERVER_ERROR = '#/components/responses/ServerError';

}
