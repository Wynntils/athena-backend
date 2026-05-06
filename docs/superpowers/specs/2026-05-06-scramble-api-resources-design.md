# Scramble API Resources Design

**Date:** 2026-05-06  
**Branch:** feat/api-docs  
**Goal:** Wire up Laravel API Resources across all active routes so Scramble can auto-generate response schemas from `toArray()` return types, with minimal manual documentation.

---

## Scope

Active routes are determined by cross-referencing `urls.json` (the canonical list of URLs the client actually calls). Anything not listed there is deprecated in-place for manual review and removal.

---

## 1. Deprecations

Add `/** @deprecated */` PHPDoc to the following controller methods. Scramble will emit `deprecated: true` in the OpenAPI spec. No routes are removed — the user will handle removal separately.

### VersionController
- `changelog` (GET `version/changelog/{version}`) — not in urls.json; only the v2 between-versions route is used
- `download` (GET `version/download/{version}/{stream}`) — has historical GA4 traffic but removed from client

### UserController
- `getInfo` (GET `user/getInfo/{user}`)
- `getConfigs` (GET `user/getConfigs`)
- `getInfoV2` (POST `v2/user/getInfo`)

### CacheController
- `getHashes` (GET `cache/getHashes`)

### CacheManager — inactive cache types
The following cache types are no longer called by the client. Deprecate the corresponding cache class and the `$cacheTable` entry in `CacheManager`:
- `gatheringSpots` (`GatheringSpots`)
- `ingredientList` (`IngredientList`)
- `itemList` (`ItemList`)
- `mapLocations` (`MapLocations`)
- `territoryList` v1 (`TerritoryList`) — only the v2 version is used
- `guildListWithColors` (`GuildListWithColors`)

### CapeController (all methods)
- `getCape`, `getUserCape`, `list`, `queueGetCape`, `queueList`, `uploadCape`, `approveCape`, `banCape`

### GuildController
- `setColor` (POST `guilds/setColor/{apiKey}`)

### TelemetryController
- `sendGatheringSpot` (POST `telemetry/sendGatheringSpot`)

### PatreonController
- `webhook`, `list`

### WebhookController
- `github`

### LegacyApiController (all methods)
- `getUserData`, `getLinkedUsersData`, `setAccountType`, `updateCosmetics`, `setGuildColor`, `setUserPassword`, `getUserByPassword`, `getUserConfig`
- Note: these routes have GA4 traffic (internal tooling), but are not in urls.json and are not client-facing.

---

## 2. Resources

All resources live in `app/Http/Resources/`. Each returns a typed array so Scramble can infer the schema from the `toArray()` return type without manual annotations.

### UserResource *(update existing)*

Used by: `POST user/getInfo` (`getInfoPost` / `getInfoLegacy`)

Minimal shape — only what `getInfoPost` currently returns. `getInfoPost` is updated to `return new UserResource($user)` instead of its current inline array.

```php
return [
    'user' => [
        'accountType' => AccountType,   // enum
        'cosmetics' => [
            'hasCape'   => bool,
            'hasElytra' => bool,
            'hasEars'   => bool,
            'texture'   => string,       // base64 PNG
        ],
    ],
];
```

### PublicKeyResource *(new)*

Used by: `GET auth/getPublicKey`

```php
return [
    'publicKeyIn' => string,   // hex-encoded public key
];
```

### AuthResponseResource *(new)*

Used by: `POST auth/responseEncryption`

```php
return [
    'message'     => string,
    'authToken'   => string,
    'configFiles' => array,    // list of config file names
    'hashes'      => array,    // cache hash map keyed by cache name
];
```

### VersionResource *(new)*

Used by: `GET version/latest/{stream}`

```php
return [
    'version'            => string,       // semver tag e.g. "v4.1.6"
    'url'                => string,       // download URL
    'md5'                => string|null,
    'changelog'          => string,       // URL to the changelog route
    'supportedMcVersion' => string|null,  // only present when asset embeds MC version
];
```

### ChangelogBetweenResource *(new)*

Used by: `GET v2/version/changelog/{version1}/{version2}`

```php
return [
    'from'       => string,
    'to'         => string,
    'changelogs' => array,   // map of semver tag => markdown string, ordered oldest→newest
];
```

### CrashReportResource *(new)*

Used by: `POST crash/report`

```php
return [
    'message' => string,
    'hash'    => string,   // MD5 of normalised stack trace
];
```

---

## 3. Cache Resources

One resource per active cache type. All live in `app/Http/Resources/Cache/`.

The `CacheController::getCache` and `getCacheV2` methods cannot return a single resource directly because the cache name is dynamic. Instead, each cache class's `generate()` result is wrapped in the appropriate resource before the controller returns it. The controller resolves which resource to use based on the cache name.

### GuildListCacheResource

Used by: `GET cache/get/guildList`

```php
// array of guild objects
return [
    [
        '_id'    => string,
        'name'   => string,
        'prefix' => string,
        'color'  => string,
    ],
    // ...
];
```

### ItemWeightsCacheResource

Used by: `GET cache/get/itemWeights`

```php
return [
    'wynnpool' => array,   // map of item name => weight name => identification weights
    'nori'     => array,   // map of item name => identification weights (scaled)
];
```

### LeaderboardCacheResource

Used by: `GET cache/get/leaderboard`

```php
// map of leaderboard type => rank => uuid or guild name
return [
    'leaderboardType' => [
        '1' => string,
        '2' => string,
        // ...up to 9
    ],
    // ...
];
```

### ServerListCacheResource

Used by: `GET cache/get/serverList`

```php
return [
    'servers' => [
        'serverName' => [
            'firstSeen' => int,       // Unix ms timestamp
            'players'   => string[],  // list of player usernames
        ],
        // ...
    ],
];
```

### TerritoryListCacheResource

Used by: `GET v2/cache/get/territoryList`

```php
// array of territory objects (v2 shape passes Wynn API structure through with guild color added)
return [
    [
        'territory' => string,
        'guild' => [
            'name'   => string,
            'prefix' => string,
            'color'  => string,
        ],
        'acquired' => string,   // ISO 8601 timestamp
        'location' => [
            'start' => int[],   // [x, z]
            'end'   => int[],   // [x, z]
        ],
    ],
    // ...
];
```

---

## 4. Out of Scope

- Route removal: deferred — user reviews deprecated routes from the generated OpenAPI spec.
- Legacy `api/*` route resource updates: routes are deprecated, so no resources needed.
- Cache types not in urls.json: deprecated, no resources created.
