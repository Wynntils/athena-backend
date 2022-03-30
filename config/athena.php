<?php

return [
    'general' => [
        'userAgent' => 'WynntilsAthena/2.0.0',
        'apiKey' => env('MASTER_TOKEN')
    ],

    'capes' => [
        'token' => env('CAPE_TOKEN')
    ],

    'webhook' => [
        'discord' => [
            'webhook' => [
                'log' => env('DISCORD_LOG_WEBHOOK'),
                'capes' => env('DISCORD_CAPE_WEBHOOK'),
            ],
            'username' => env('DISCORD_USERNAME', 'Athena'),
            'avatar' => env('DISCORD_AVATAR', 'https://cdn.wynntils.com/athena_logo_1600x1600.png')
        ]
    ],

    'api' => [
        'wynn' => [
            'apiKey' => env('WYNN_APIKEY'),
            'territories' => 'https://api.wynncraft.com/public_api.php?action=territoryList',
            'mapLocations' => 'https://api.wynncraft.com/public_api.php?action=mapLocations',
            'mapLabels' => 'https://raw.githubusercontent.com/Wynntils/Data-Storage/master/map-labels.json',
            'npcLocations' => 'https://raw.githubusercontent.com/Wynntils/Data-Storage/master/npc-locations.json',
            'items' => 'https://api.wynncraft.com/public_api.php?action=itemDB&category=all',
            'guildInfo' => 'https://api.wynncraft.com/public_api.php?action=guildStats&command=',
            'onlinePlayers' => 'https://api.wynncraft.com/public_api.php?action=onlinePlayers',
            'leaderboards' => 'https://api.wynncraft.com/v2/leaderboards/player/',
            'ingredients' => 'https://api.wynncraft.com/v2/ingredient/search/skills/%5Etailoring,armouring,jeweling,cooking,woodworking,weaponsmithing,alchemism,scribing',
            'builderIds' => 'https://wynnbuilder.github.io/compress.json',
        ],

        'mojang' => [
            'auth' => env('MOJANG_AUTH_SERVER',
                'https://sessionserver.mojang.com/session/minecraft/hasJoined?username=%s&serverId=%s')
        ]
    ]
];
