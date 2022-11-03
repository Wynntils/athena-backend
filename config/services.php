<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'patreon' => [
        'webhook_secret' => env('PATREON_WEBHOOK_SECRET'),
        'discord_webhook' => env('PATREON_DISCORD_WEBHOOK'),
        'access_token' => env('PATREON_ACCESS_TOKEN')
    ],

    'github' => [
        'webhook_secret' => env('GITHUB_WEBHOOK_SECRET'),
        'webhooks' => [
            'Wynntils/Wynntils' => env('GITHUB_WYNNTILS_WEBHOOKS'),
            'Wynntils/Artemis' => env('GITHUB_ARTEMIS_WEBHOOKS'),
        ]
    ]

];
