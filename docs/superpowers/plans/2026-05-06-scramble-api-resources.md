# Scramble API Resources Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers-extended-cc:subagent-driven-development (recommended) or superpowers-extended-cc:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Wire up typed Laravel API Resources for all active routes so Scramble auto-generates accurate response schemas, and mark all inactive routes/cache types as deprecated.

**Architecture:** Each active route's controller method returns a typed `JsonResource` subclass directly (not `response()->json()`). Scramble reads the `toArray()` return type annotation to produce the OpenAPI schema. Cache routes use a name→resource dispatch map in `CacheController` since the route is dynamic and cannot be auto-inferred by Scramble. Inactive routes receive `/** @deprecated */` PHPDoc so Scramble emits `deprecated: true`.

**Tech Stack:** Laravel 10, PHP 8.4, `dedoc/scramble`, PHPUnit feature tests.

---

## File Map

**New files:**
- `app/Http/Resources/PublicKeyResource.php`
- `app/Http/Resources/AuthResponseResource.php`
- `app/Http/Resources/VersionResource.php`
- `app/Http/Resources/ChangelogBetweenResource.php`
- `app/Http/Resources/CrashReportResource.php`
- `app/Http/Resources/Cache/GuildListCacheResource.php`
- `app/Http/Resources/Cache/ItemWeightsCacheResource.php`
- `app/Http/Resources/Cache/LeaderboardCacheResource.php`
- `app/Http/Resources/Cache/ServerListCacheResource.php`
- `app/Http/Resources/Cache/TerritoryListCacheResource.php`
- `tests/Feature/ResourceSchemaTest.php`

**Modified files:**
- `app/Http/Resources/UserResource.php` — rewritten to minimal getInfoPost shape
- `app/Http/Controllers/UserController.php` — deprecate 3 methods; update `getInfoPost` return type
- `app/Http/Controllers/AuthController.php` — update 2 methods to return resources
- `app/Http/Controllers/VersionController.php` — deprecate 2 methods; update 2 methods to return resources
- `app/Http/Controllers/CacheController.php` — add resource dispatch map; deprecate `getHashes`
- `app/Http/Controllers/CrashReportController.php` — update `report` to return resource
- `app/Http/Controllers/CapeController.php` — deprecate all 8 methods
- `app/Http/Controllers/GuildController.php` — deprecate `setColor`
- `app/Http/Controllers/TelemetryController.php` — deprecate `sendGatheringSpot`
- `app/Http/Controllers/PatreonController.php` — deprecate `webhook`, `list`
- `app/Http/Controllers/WebhookController.php` — deprecate `github`
- `app/Http/Controllers/LegacyApiController.php` — deprecate all 8 methods
- `app/Http/Libraries/Requests/Cache/GatheringSpots.php` — add class-level `@deprecated`
- `app/Http/Libraries/Requests/Cache/IngredientList.php` — add class-level `@deprecated`
- `app/Http/Libraries/Requests/Cache/ItemList.php` — add class-level `@deprecated`
- `app/Http/Libraries/Requests/Cache/MapLocations.php` — add class-level `@deprecated`
- `app/Http/Libraries/Requests/Cache/TerritoryList.php` — add class-level `@deprecated`
- `app/Http/Libraries/Requests/Cache/GuildListWithColors.php` — add class-level `@deprecated`

---

## Task 1: Deprecate all inactive routes and cache types

**Goal:** Add `/** @deprecated */` to every inactive controller method and cache class so Scramble marks them and they're visible for review.

**Files:**
- Modify: `app/Http/Controllers/UserController.php`
- Modify: `app/Http/Controllers/VersionController.php`
- Modify: `app/Http/Controllers/CacheController.php`
- Modify: `app/Http/Controllers/CapeController.php`
- Modify: `app/Http/Controllers/GuildController.php`
- Modify: `app/Http/Controllers/TelemetryController.php`
- Modify: `app/Http/Controllers/PatreonController.php`
- Modify: `app/Http/Controllers/WebhookController.php`
- Modify: `app/Http/Controllers/LegacyApiController.php`
- Modify: `app/Http/Libraries/Requests/Cache/GatheringSpots.php`
- Modify: `app/Http/Libraries/Requests/Cache/IngredientList.php`
- Modify: `app/Http/Libraries/Requests/Cache/ItemList.php`
- Modify: `app/Http/Libraries/Requests/Cache/MapLocations.php`
- Modify: `app/Http/Libraries/Requests/Cache/TerritoryList.php`
- Modify: `app/Http/Libraries/Requests/Cache/GuildListWithColors.php`

**Acceptance Criteria:**
- [ ] All inactive controller methods have `/** @deprecated */` immediately above their `public function` line
- [ ] All six inactive cache classes have `/** @deprecated */` immediately above their `class` line
- [ ] `php artisan scramble:export` runs without errors
- [ ] `api.json` contains `"deprecated": true` for each marked route

**Verify:** `php artisan scramble:export && grep -c '"deprecated": true' api.json` → count ≥ 20

**Steps:**

- [ ] **Step 1: Deprecate UserController methods**

In `app/Http/Controllers/UserController.php`, add `/** @deprecated */` above each of these three methods:

```php
/** @deprecated */
public function getInfo($user): JsonResponse

/** @deprecated */
public function getConfigs(): JsonResponse

/** @deprecated */
public function getInfoV2(UserRequest $request): JsonResponse
```

- [ ] **Step 2: Deprecate VersionController methods**

In `app/Http/Controllers/VersionController.php`, add `/** @deprecated */` above:

```php
/** @deprecated */
public function changelog(Request $request, $version): JsonResponse

/** @deprecated */
public function download($version, $stream, $modloader = 'fabric'): RedirectResponse|JsonResponse
```

- [ ] **Step 3: Deprecate CacheController::getHashes**

In `app/Http/Controllers/CacheController.php`:

```php
/** @deprecated */
public function getHashes(): JsonResponse
```

- [ ] **Step 4: Deprecate all CapeController methods**

In `app/Http/Controllers/CapeController.php`, add `/** @deprecated */` above each of these eight methods:
`getCape`, `getUserCape`, `list`, `queueGetCape`, `queueList`, `uploadCape`, `approveCape`, `banCape`.

Example — apply the same pattern to all eight:

```php
/** @deprecated */
public function getCape($capeId): BinaryFileResponse
```

- [ ] **Step 5: Deprecate GuildController::setColor**

In `app/Http/Controllers/GuildController.php`:

```php
/** @deprecated */
public function setColor(Request $request)
```

- [ ] **Step 6: Deprecate TelemetryController::sendGatheringSpot**

In `app/Http/Controllers/TelemetryController.php`:

```php
/** @deprecated */
public function sendGatheringSpot(...)
```

- [ ] **Step 7: Deprecate PatreonController methods**

In `app/Http/Controllers/PatreonController.php`, add `/** @deprecated */` above `webhook` and `list`.

- [ ] **Step 8: Deprecate WebhookController::github**

In `app/Http/Controllers/WebhookController.php`:

```php
/** @deprecated */
public function github(...)
```

- [ ] **Step 9: Deprecate all LegacyApiController methods**

In `app/Http/Controllers/LegacyApiController.php`, add `/** @deprecated */` above each of these eight methods:
`getUserData`, `getLinkedUsersData`, `setAccountType`, `updateCosmetics`, `setGuildColor`, `setUserPassword`, `getUserByPassword`, `getUserConfig`.

- [ ] **Step 10: Deprecate inactive cache classes**

Add `/** @deprecated */` immediately above the `class` declaration in each of these six files:

`app/Http/Libraries/Requests/Cache/GatheringSpots.php`:
```php
/** @deprecated */
class GatheringSpots implements CacheContract
```

Apply identically to:
- `app/Http/Libraries/Requests/Cache/IngredientList.php`
- `app/Http/Libraries/Requests/Cache/ItemList.php`
- `app/Http/Libraries/Requests/Cache/MapLocations.php`
- `app/Http/Libraries/Requests/Cache/TerritoryList.php` (v1 — `App\Http\Libraries\Requests\Cache\TerritoryList`, not the v2 version)
- `app/Http/Libraries/Requests/Cache/GuildListWithColors.php`

- [ ] **Step 11: Verify and commit**

```bash
php artisan scramble:export
grep -c '"deprecated": true' api.json
```

Expected: at least 20 matches.

```bash
git add app/Http/Controllers/ app/Http/Libraries/Requests/Cache/ api.json
git commit -m "chore: mark inactive routes and cache types as deprecated"
```

---

## Task 2: Update UserResource and UserController::getInfoPost

**Goal:** Rewrite `UserResource::toArray()` to the minimal shape used by `getInfoPost`, and update `getInfoPost` to return the resource directly so Scramble can infer the response schema.

**Files:**
- Modify: `app/Http/Resources/UserResource.php`
- Modify: `app/Http/Controllers/UserController.php`
- Create: `tests/Feature/ResourceSchemaTest.php`

**Acceptance Criteria:**
- [ ] `POST /user/getInfo` with a valid `uuid` body returns `{user: {accountType, cosmetics: {hasCape, hasElytra, hasEars, texture}}}`
- [ ] `UserController::getInfoPost` return type is `UserResource`
- [ ] `UserResource::toArray()` has a typed `@return` PHPDoc
- [ ] Scramble export contains the `user` object schema under the `getInfoLegacy` operation

**Verify:** `php artisan test --filter=ResourceSchemaTest::test_get_info_returns_user_resource_shape`

**Steps:**

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/ResourceSchemaTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Enums\AccountType;
use Tests\TestCase;

class ResourceSchemaTest extends TestCase
{
    public function test_get_info_returns_user_resource_shape(): void
    {
        $user = User::factory()->create([
            'account_type' => AccountType::NORMAL,
            'cosmetic_info' => [],
        ]);

        $response = $this->postJson('/user/getInfo', ['uuid' => $user->id]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'accountType',
                    'cosmetics' => ['hasCape', 'hasElytra', 'hasEars', 'texture'],
                ],
            ]);
    }
}
```

- [ ] **Step 2: Run test to confirm it fails or identifies the current shape**

```bash
php artisan test --filter=ResourceSchemaTest::test_get_info_returns_user_resource_shape
```

Note: if `User::factory()` doesn't exist, run with a real UUID from the DB or mock. Check `database/factories/UserFactory.php` — if absent, add a minimal one:

```php
<?php
// database/factories/UserFactory.php
namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'id'           => Str::uuid()->toString(),
            'username'     => $this->faker->userName(),
            'account_type' => AccountType::NORMAL,
            'auth_token'   => Str::uuid()->toString(),
            'cosmetic_info' => [],
        ];
    }
}
```

Also add `use HasFactory;` to `app/Models/User.php` and `use Illuminate\Database\Eloquent\Factories\HasFactory;`.

- [ ] **Step 3: Rewrite UserResource**

Replace the entire contents of `app/Http/Resources/UserResource.php`:

```php
<?php

namespace App\Http\Resources;

use App\Http\Libraries\CapeManager;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    /**
     * @return array{user: array{accountType: string, cosmetics: array{hasCape: bool, hasElytra: bool, hasEars: bool, texture: string}}}
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'accountType' => $this->account_type,
                'cosmetics'   => [
                    'hasCape'   => $this->hasCape(),
                    'hasElytra' => $this->hasElytra(),
                    'hasEars'   => $this->hasPart('ears'),
                    'texture'   => CapeManager::instance()->getCapeAsBase64($this->getFormattedTexture(), true),
                ],
            ],
        ];
    }
}
```

- [ ] **Step 4: Update UserController::getInfoPost**

In `app/Http/Controllers/UserController.php`, add `use App\Http\Resources\UserResource;` to the imports if not already present, then replace `getInfoPost`:

```php
public function getInfoPost(UserRequest $request): UserResource
{
    $user = $this->getUser($request->validated('uuid'));

    return new UserResource($user);
}
```

- [ ] **Step 5: Run test and verify it passes**

```bash
php artisan test --filter=ResourceSchemaTest::test_get_info_returns_user_resource_shape
```

Expected: PASS

- [ ] **Step 6: Verify Scramble picks up the schema**

```bash
php artisan scramble:export
grep -A 20 '"getInfoLegacy"' api.json | grep -A 5 '"user"'
```

Expected: the `user` object with `accountType` and `cosmetics` properties is present in the schema.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Resources/UserResource.php app/Http/Controllers/UserController.php tests/Feature/ResourceSchemaTest.php database/factories/UserFactory.php api.json
git commit -m "feat: update UserResource to minimal shape and wire getInfoPost"
```

---

## Task 3: Create Auth resources and update AuthController

**Goal:** Create `PublicKeyResource` and `AuthResponseResource`, update both `AuthController` methods to return them so Scramble documents the auth response schemas.

**Files:**
- Create: `app/Http/Resources/PublicKeyResource.php`
- Create: `app/Http/Resources/AuthResponseResource.php`
- Modify: `app/Http/Controllers/AuthController.php`

**Acceptance Criteria:**
- [ ] `GET /auth/getPublicKey` response is documented with `publicKeyIn: string`
- [ ] `POST /auth/responseEncryption` response is documented with `message`, `authToken`, `configFiles`, `hashes`
- [ ] Both controller methods return their resource directly (not `response()->json()`)
- [ ] Scramble export contains schemas for both auth operations

**Verify:** `php artisan scramble:export && grep -A 5 '"publicKeyIn"' api.json`

**Steps:**

- [ ] **Step 1: Create PublicKeyResource**

Create `app/Http/Resources/PublicKeyResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicKeyResource extends JsonResource
{
    /**
     * @return array{publicKeyIn: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'publicKeyIn' => $this->resource,
        ];
    }
}
```

- [ ] **Step 2: Create AuthResponseResource**

Create `app/Http/Resources/AuthResponseResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResponseResource extends JsonResource
{
    /**
     * @return array{message: string, authToken: string, configFiles: array<string, mixed>, hashes: array<string, string|null>}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
```

`$this->resource` here is the response array already built in the controller — the resource wraps it for Scramble's benefit.

- [ ] **Step 3: Update AuthController**

In `app/Http/Controllers/AuthController.php`, add imports:

```php
use App\Http\Resources\AuthResponseResource;
use App\Http\Resources\PublicKeyResource;
```

Replace `getPublicKey`:

```php
public function getPublicKey(): PublicKeyResource
{
    return new PublicKeyResource(bin2hex(MinecraftFakeAuth::instance()->getPublicKey()));
}
```

Replace the final return statement of `responseEncryption` (keep all the logic above it unchanged — only the return value changes):

```php
// Replace this at the end of responseEncryption():
return response()->json($response);
// With:
return new AuthResponseResource($response);
```

Also update the return type of `responseEncryption` from `JsonResponse` to `AuthResponseResource|JsonResponse` (error paths still return `JsonResponse`).

- [ ] **Step 4: Verify Scramble export**

```bash
php artisan scramble:export
grep -A 5 '"publicKeyIn"' api.json
grep -A 5 '"authToken"' api.json
```

Expected: both fields appear in their respective operation schemas.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Resources/PublicKeyResource.php app/Http/Resources/AuthResponseResource.php app/Http/Controllers/AuthController.php api.json
git commit -m "feat: add PublicKeyResource and AuthResponseResource for auth endpoints"
```

---

## Task 4: Create Version resources and update VersionController

**Goal:** Create `VersionResource` and `ChangelogBetweenResource`, update `latest` and `changelogBetween` to return them directly.

**Files:**
- Create: `app/Http/Resources/VersionResource.php`
- Create: `app/Http/Resources/ChangelogBetweenResource.php`
- Modify: `app/Http/Controllers/VersionController.php`

**Acceptance Criteria:**
- [ ] `GET /version/latest/{stream}` response schema includes `version`, `url`, `md5`, `changelog`, optional `supportedMcVersion`
- [ ] `GET /v2/version/changelog/{version1}/{version2}` response schema includes `from`, `to`, `changelogs`
- [ ] Both controller methods return their resource directly
- [ ] Existing `VersionControllerTest` still passes

**Verify:** `php artisan test --filter=VersionControllerTest && php artisan scramble:export && grep -A 5 '"supportedMcVersion"' api.json`

**Steps:**

- [ ] **Step 1: Create VersionResource**

Create `app/Http/Resources/VersionResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VersionResource extends JsonResource
{
    /**
     * @return array{version: string, url: string, md5: string|null, changelog: string, supportedMcVersion?: string}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
```

`$this->resource` is the response array already built by `latest()`. The resource wraps it so Scramble sees the typed return.

- [ ] **Step 2: Create ChangelogBetweenResource**

Create `app/Http/Resources/ChangelogBetweenResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChangelogBetweenResource extends JsonResource
{
    /**
     * @return array{from: string, to: string, changelogs: array<string, string>}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
```

- [ ] **Step 3: Update VersionController::latest**

In `app/Http/Controllers/VersionController.php`, add imports:

```php
use App\Http\Resources\ChangelogBetweenResource;
use App\Http\Resources\VersionResource;
```

In `latest()`, find the final success return (after building `$response`):

```php
// Replace:
return response()->json($response)->header('Vary', 'User-Agent');
// With:
return (new VersionResource($response))->response()->header('Vary', 'User-Agent');
```

Update the method return type from `JsonResponse` to `VersionResource|JsonResponse` (error paths still return `JsonResponse`).

- [ ] **Step 4: Update VersionController::changelogBetween**

In `changelogBetween()`, replace the final return:

```php
// Replace:
return response()->json([
    'from' => $from['tag_name'],
    'to' => $to['tag_name'],
    'changelogs' => $perVersionChangelogs,
]);
// With:
return new ChangelogBetweenResource([
    'from'       => $from['tag_name'],
    'to'         => $to['tag_name'],
    'changelogs' => $perVersionChangelogs,
]);
```

Update the return type to `ChangelogBetweenResource|JsonResponse`.

- [ ] **Step 5: Run existing tests and verify export**

```bash
php artisan test --filter=VersionControllerTest
php artisan scramble:export
grep -A 5 '"supportedMcVersion"' api.json
grep -A 5 '"changelogs"' api.json
```

Expected: tests pass, both schemas present in export.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Resources/VersionResource.php app/Http/Resources/ChangelogBetweenResource.php app/Http/Controllers/VersionController.php api.json
git commit -m "feat: add VersionResource and ChangelogBetweenResource for version endpoints"
```

---

## Task 5: Create CrashReportResource and update CrashReportController

**Goal:** Create `CrashReportResource` and update `CrashReportController::report` to return it.

**Files:**
- Create: `app/Http/Resources/CrashReportResource.php`
- Modify: `app/Http/Controllers/CrashReportController.php`

**Acceptance Criteria:**
- [ ] `POST /crash/report` response schema includes `message: string` and `hash: string`
- [ ] `report()` return type is `CrashReportResource`
- [ ] Scramble export contains the crash report schema

**Verify:** `php artisan scramble:export && grep -A 5 '"trace_hash"' api.json || grep -A 5 '"hash"' api.json`

**Steps:**

- [ ] **Step 1: Create CrashReportResource**

Create `app/Http/Resources/CrashReportResource.php`:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CrashReportResource extends JsonResource
{
    /**
     * @return array{message: string, hash: string}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
```

- [ ] **Step 2: Update CrashReportController::report**

In `app/Http/Controllers/CrashReportController.php`, add import:

```php
use App\Http\Resources\CrashReportResource;
```

Find the final return in `report()`:

```php
// Replace:
return response()->json(['message' => 'Crash report logged successfully.', 'hash' => $crashReport->trace_hash]);
// With:
return new CrashReportResource([
    'message' => 'Crash report logged successfully.',
    'hash'    => $crashReport->trace_hash,
]);
```

Update the return type of `report()` from (whatever it currently is) to `CrashReportResource`.

- [ ] **Step 3: Verify**

```bash
php artisan scramble:export
grep -A 8 'crash' api.json | grep -E '"message"|"hash"'
```

Expected: both `message` and `hash` appear in the crash report operation schema.

- [ ] **Step 4: Commit**

```bash
git add app/Http/Resources/CrashReportResource.php app/Http/Controllers/CrashReportController.php api.json
git commit -m "feat: add CrashReportResource for crash/report endpoint"
```

---

## Task 6: Create cache resource classes

**Goal:** Create one typed resource class per active cache type. Each resource wraps the full array returned by the cache's `generate()` method and declares its shape via PHPDoc so the schema is captured.

**Files:**
- Create: `app/Http/Resources/Cache/GuildListCacheResource.php`
- Create: `app/Http/Resources/Cache/ItemWeightsCacheResource.php`
- Create: `app/Http/Resources/Cache/LeaderboardCacheResource.php`
- Create: `app/Http/Resources/Cache/ServerListCacheResource.php`
- Create: `app/Http/Resources/Cache/TerritoryListCacheResource.php`

**Acceptance Criteria:**
- [ ] All five resource classes exist with correct namespace `App\Http\Resources\Cache`
- [ ] Each `toArray()` has a typed `@return` PHPDoc matching the actual generate() output shape
- [ ] No existing behaviour changes — these classes are created but not yet wired into the controller (that's Task 7)

**Verify:** `php artisan tinker --execute="new App\Http\Resources\Cache\ServerListCacheResource([]); echo 'ok';"` → `ok`

**Steps:**

- [ ] **Step 1: Create GuildListCacheResource**

`GuildList::generate()` maps Guild models to arrays with `_id` (the guild name), `prefix`, and `color` fields — the `id` column is remapped to `_id` and the original `id` key is removed.

Create `app/Http/Resources/Cache/GuildListCacheResource.php`:

```php
<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuildListCacheResource extends JsonResource
{
    /**
     * @return array<int, array{_id: string, prefix: string, color: string}>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
```

- [ ] **Step 2: Create ItemWeightsCacheResource**

`ItemWeights::generate()` fetches weights from Wynnpool and Nori and returns them in two top-level keys.

Create `app/Http/Resources/Cache/ItemWeightsCacheResource.php`:

```php
<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemWeightsCacheResource extends JsonResource
{
    /**
     * @return array{wynnpool: array<string, array<string, array<string, float>>>, nori: array<string, array<string, float>>}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
```

- [ ] **Step 3: Create LeaderboardCacheResource**

`Leaderboard::generate()` returns a map of leaderboard type → rank (1–9) → UUID or guild name string.

Create `app/Http/Resources/Cache/LeaderboardCacheResource.php`:

```php
<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardCacheResource extends JsonResource
{
    /**
     * @return array<string, array<string, string>>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
```

- [ ] **Step 4: Create ServerListCacheResource**

`ServerList::generate()` returns a `servers` map where each key is a server name and the value has `firstSeen` (Unix ms int) and `players` (array of username strings).

Create `app/Http/Resources/Cache/ServerListCacheResource.php`:

```php
<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServerListCacheResource extends JsonResource
{
    /**
     * @return array{servers: array<string, array{firstSeen: int, players: string[]}>}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
```

- [ ] **Step 5: Create TerritoryListCacheResource**

`v2/TerritoryList::generate()` passes the Wynn API territory structure through, adding a `guild.color` field to each territory entry.

Create `app/Http/Resources/Cache/TerritoryListCacheResource.php`:

```php
<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TerritoryListCacheResource extends JsonResource
{
    /**
     * @return array<string, array{
     *     guild: array{name: string|null, prefix: string|null, color: string},
     *     acquired: string,
     *     location: array{start: array{int, int}, end: array{int, int}}
     * }>
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
```

- [ ] **Step 6: Verify classes load**

```bash
php artisan tinker --execute="
    new App\Http\Resources\Cache\GuildListCacheResource([]);
    new App\Http\Resources\Cache\ItemWeightsCacheResource([]);
    new App\Http\Resources\Cache\LeaderboardCacheResource([]);
    new App\Http\Resources\Cache\ServerListCacheResource([]);
    new App\Http\Resources\Cache\TerritoryListCacheResource([]);
    echo 'all ok';
"
```

Expected: `all ok`

- [ ] **Step 7: Commit**

```bash
git add app/Http/Resources/Cache/
git commit -m "feat: add typed cache resource classes for all active cache types"
```

---

## Task 7: Wire cache resources into CacheController

**Goal:** Update `CacheController::getCache` and `getCacheV2` to wrap their responses in the appropriate resource class, enforcing the declared shape at runtime. Add `@response` PHPDoc for Scramble since the route is dynamic and cannot be auto-inferred.

**Files:**
- Modify: `app/Http/Controllers/CacheController.php`

**Acceptance Criteria:**
- [ ] `GET /cache/get/guildList` response is wrapped by `GuildListCacheResource`
- [ ] `GET /cache/get/serverList` response is wrapped by `ServerListCacheResource`
- [ ] `GET /cache/get/itemWeights` response is wrapped by `ItemWeightsCacheResource`
- [ ] `GET /cache/get/leaderboard` response is wrapped by `LeaderboardCacheResource`
- [ ] `GET /v2/cache/get/territoryList` response is wrapped by `TerritoryListCacheResource`
- [ ] Cache-Control, ETag, and timestamp headers are still present on all cache responses
- [ ] `php artisan scramble:export` completes without errors

**Verify:** `php artisan scramble:export` → exit 0

**Steps:**

- [ ] **Step 1: Update CacheController**

Replace the full contents of `app/Http/Controllers/CacheController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Http\Libraries\CacheManager;
use App\Http\Resources\Cache\GuildListCacheResource;
use App\Http\Resources\Cache\ItemWeightsCacheResource;
use App\Http\Resources\Cache\LeaderboardCacheResource;
use App\Http\Resources\Cache\ServerListCacheResource;
use App\Http\Resources\Cache\TerritoryListCacheResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class CacheController extends Controller
{
    private static array $resourceMap = [
        'guildList'   => GuildListCacheResource::class,
        'itemWeights' => ItemWeightsCacheResource::class,
        'leaderboard' => LeaderboardCacheResource::class,
        'serverList'  => ServerListCacheResource::class,
    ];

    private static array $v2ResourceMap = [
        'territoryList' => TerritoryListCacheResource::class,
    ];

    public function getCache($cacheName): JsonResponse
    {
        $cache = CacheManager::getCacheClass($cacheName, 'v1');
        if (! $cache) {
            return response()->json(['message' => "There's not a cache with the provided name."], 404);
        }

        $data = CacheManager::generateCache($cacheName, 'v1');

        $resourceClass = self::$resourceMap[$cacheName] ?? null;
        $response = $resourceClass
            ? (new $resourceClass($data))->response()
            : response()->json($data);

        return $response
            ->header('timestamp', currentTimeMillis())
            ->setCache([
                'max_age'  => $cache->refreshRate(),
                's_maxage' => $cache->refreshRate(),
                'public'   => true,
            ])
            ->setExpires(now()->addSeconds($cache->refreshRate()))
            ->setEtag(Cache::get($cacheName.'.hash'));
    }

    public function getCacheV2($cacheName): JsonResponse
    {
        $cache = CacheManager::getCacheClass($cacheName, 'v2');
        if (! $cache) {
            return response()->json(['message' => "There's not a cache with the provided name."], 404);
        }

        $data = CacheManager::generateCache($cacheName, 'v2');
        $ttl = $cache->refreshRate();
        $key = "v2.$cacheName";

        $resourceClass = self::$v2ResourceMap[$cacheName] ?? null;
        $response = $resourceClass
            ? (new $resourceClass($data))->response()
            : response()->json($data);

        return $response
            ->header('timestamp', currentTimeMillis())
            ->setCache(['max_age' => $ttl, 's_maxage' => $ttl, 'public' => true])
            ->setExpires(now()->addSeconds($ttl))
            ->setEtag(Cache::get($key.'.hash'));
    }

    /** @deprecated */
    public function getHashes(): JsonResponse
    {
        return response()->json(['result' => CacheManager::getHashes(), 'message' => 'Successfully grabbed cache hashes.'], 200);
    }
}
```

Note: `JsonResource::response()` returns a `JsonResponse`, so the fluent `->header()`, `->setCache()`, `->setExpires()`, `->setEtag()` chain works identically to the previous `response()->json()` chain.

- [ ] **Step 2: Verify the export and confirm headers survive**

```bash
php artisan scramble:export
```

Expected: exits 0 with no errors.

To confirm headers still work, hit a cache endpoint manually (requires a running dev server):

```bash
php artisan serve &
curl -si http://127.0.0.1:8000/cache/get/serverList | grep -i 'cache-control\|etag\|timestamp'
kill %1
```

Expected: `cache-control`, `etag`, and `timestamp` headers are present in the response.

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/CacheController.php api.json
git commit -m "feat: wire cache resources into CacheController dispatch map"
```
