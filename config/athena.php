<?php

return [
    'general' => [
        'userAgent' => 'WynntilsAthena/2.0.0',
        'apiKey' => env('MASTER_TOKEN')
    ],

    'debug' => [
        'users' => [
            '879be29a-bcca-43d6-978a-321a4241c392', // Scyu
            '4e84e744-53da-4fc4-a226-09011f372c37' // v8j
        ]
    ],

    'capes' => [
        'token' => env('CAPE_TOKEN')
    ],

    'webhook' => [
        'discord' => [
            'webhook' => [
                'log' => env('DISCORD_LOG_WEBHOOK'),
                'capes' => env('DISCORD_CAPE_WEBHOOK'),
                'crash' => env('DISCORD_CRASH_WEBHOOK'),
            ],
            'username' => env('DISCORD_USERNAME', 'Athena'),
            'avatar' => env('DISCORD_AVATAR', 'https://cdn.wynntils.com/athena_logo_1600x1600.png')
        ]
    ],

    'api' => [
        'wynn' => [
            'apiKey' => env('WYNN_APIKEY'),

            'mapLabels' => 'https://raw.githubusercontent.com/Wynntils/Data-Storage/master/map-labels.json',
            'npcLocations' => 'https://raw.githubusercontent.com/Wynntils/Data-Storage/master/npc-locations.json',
            'builderIds' => 'https://wynnbuilder.github.io/compress.json',

            'v2' => [
                'leaderboards' => 'https://api.wynncraft.com/v2/leaderboards/player/',
                'ingredients' => 'https://api.wynncraft.com/v2/ingredient/search/skills/%5Etailoring,armouring,jeweling,cooking,woodworking,weaponsmithing,alchemism,scribing',
            ],

            'v3' => [
                'territories' => 'https://api.wynncraft.com/v3/guild/list/territory',
                'mapLocations' => 'https://api.wynncraft.com/v3/map/locations',
                'items' => 'https://api.wynncraft.com/v3/item/database?fullResult=True',
                'guildInfo' => 'https://api.wynncraft.com/v3/guild/',
                'onlinePlayers' => 'https://api.wynncraft.com/v3/player',
            ]
        ],

        'mojang' => [
            'auth' => env('MOJANG_AUTH_SERVER',
                'https://sessionserver.mojang.com/session/minecraft/hasJoined?username=%s&serverId=%s')
        ]
    ]
];
