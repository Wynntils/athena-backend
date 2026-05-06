# Scramble API Resources Design

**Date:** 2026-05-06  
**Branch:** feat/api-docs  
**Goal:** Wire up Laravel API Resources across all active routes so Scramble can auto-generate response schemas from `toArray()` return types, with minimal manual documentation.

---

## Scope

Determined by GA4 analytics (Jan–May 2026). Routes with no traffic are deprecated in-place; routes with traffic get a resource.

---

## 1. Deprecations

Add `/** @deprecated */` PHPDoc to the following controller methods. Scramble will emit `deprecated: true` in the OpenAPI spec. No routes are removed — the user will handle removal separately.

### UserController
- `getInfo` (GET `user/getInfo/{user}`)
- `getConfigs` (GET `user/getConfigs`)
- `getInfoV2` (POST `v2/user/getInfo`)

### CacheController
- `getHashes` (GET `cache/getHashes`)

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

### LegacyApiController
- `getLinkedUsersData`, `getUserConfig`

---

## 2. Resources

All resources live in `app/Http/Resources/`. Each returns typed arrays so Scramble can infer the schema without manual annotations.

### UserResource *(update existing)*

Used by: `POST user/getInfo` (`getInfoPost` / `getInfoLegacy`)

Keeps the minimal shape `getInfoPost` currently returns inline. The legacy `api/*` routes continue to use `UserResource` via their existing `collect(new UserResource($user))` pattern — no change there.

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

`getInfoPost` is updated to `return new UserResource($user)` instead of the current inline array.

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
    'hashes'      => array,    // cache hash map
];
```

### VersionResource *(new)*

Used by: `GET version/latest/{stream}`

```php
return [
    'version'            => string,        // semver tag e.g. "v4.1.6"
    'url'                => string,        // download URL
    'md5'                => string|null,
    'changelog'          => string,        // URL to changelog route
    'supportedMcVersion' => string|null,   // conditional — only present when asset embeds MC version
];
```

### ChangelogResource *(new)*

Used by: `GET version/changelog/{version}`

```php
return [
    'version'   => string,
    'changelog' => string,   // cleaned markdown body
];
```

### ChangelogBetweenResource *(new)*

Used by: `GET v2/version/changelog/{version1}/{version2}`

```php
return [
    'from'       => string,
    'to'         => string,
    'changelogs' => array,   // map of tag => markdown string, ordered oldest→newest
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

## 3. Cache Routes — No Resource

`GET cache/get/{cache}` and `GET v2/cache/get/{cache}` return dynamic data whose shape varies per `{cache}` name (`territoryList`, `serverList`, `guildList`, etc.). A single resource cannot represent all variants.

These two methods get a `@response` PHPDoc annotation with a representative example payload (e.g. `serverList`). No resource class is created.

---

## 4. Out of Scope

- Legacy `api/*` routes: already use `UserResource` via `collect(new UserResource($user))`. That pattern is unchanged.
- Route removal: deferred to the user after reviewing the deprecated spec.
- Cache route per-name schemas: out of scope — would require one resource per cache type.
