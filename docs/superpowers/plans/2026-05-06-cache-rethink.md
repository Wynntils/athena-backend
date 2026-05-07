# Cache System Rethink Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers-extended-cc:subagent-driven-development (recommended) or superpowers-extended-cc:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace on-request lazy cache generation with scheduled queued jobs and thin controllers that read pre-built data from the cache store.

**Architecture:** Five `ShouldQueue` jobs (one per active cache) run on a dedicated `cache` Horizon queue on a fixed schedule and write results to the cache store. Six invokable controllers read from the store and return typed Scramble-compatible resources. Cold start falls back to synchronous job dispatch. All deprecated generators, `CacheManager`, `CacheContract`, and the old monolithic `CacheController` are deleted.

**Tech Stack:** Laravel queued jobs, Horizon, `Cache::forever()`, Scramble `#[Group]` attributes, PHPUnit feature/unit tests.

---

## File Map

**Create:**
- `app/Jobs/Cache/RefreshGuildListCache.php`
- `app/Jobs/Cache/RefreshServerListCache.php`
- `app/Jobs/Cache/RefreshItemWeightsCache.php`
- `app/Jobs/Cache/RefreshLeaderboardCache.php`
- `app/Jobs/Cache/RefreshTerritoryListCache.php`
- `app/Http/Controllers/Cache/GuildListController.php`
- `app/Http/Controllers/Cache/ServerListController.php`
- `app/Http/Controllers/Cache/ItemWeightsController.php`
- `app/Http/Controllers/Cache/LeaderboardController.php`
- `app/Http/Controllers/Cache/TerritoryListController.php`
- `app/Http/Controllers/Cache/HashesController.php`
- `app/Http/Resources/Cache/HashesResource.php`
- `tests/Unit/Jobs/Cache/RefreshCacheJobsTest.php`
- `tests/Feature/Cache/CacheControllersTest.php`

**Modify:**
- `config/horizon.php` — add `cache` supervisor
- `app/Console/Kernel.php` — register scheduled jobs
- `routes/api/cache.php` — replace routes with named invokable controllers
- `app/Http/Controllers/AuthController.php` — replace `CacheManager::getHashes()` with direct `Cache::get()` calls

**Delete (Task 8):**
- `app/Http/Libraries/CacheManager.php`
- `app/Http/Libraries/Requests/Cache/CacheContract.php`
- `app/Http/Libraries/Requests/Cache/GatheringSpots.php`
- `app/Http/Libraries/Requests/Cache/IngredientList.php`
- `app/Http/Libraries/Requests/Cache/ItemList.php`
- `app/Http/Libraries/Requests/Cache/MapLocations.php`
- `app/Http/Libraries/Requests/Cache/TerritoryList.php` (v1)
- `app/Http/Libraries/Requests/Cache/GuildListWithColors.php`
- `app/Http/Controllers/CacheController.php`
- `routes/api/v2/cache.php`

**Modify in Task 8 (cleanup):**
- `app/Http/Libraries/Requests/Cache/GuildList.php` — remove `implements CacheContract`, remove `refreshRate()`
- `app/Http/Libraries/Requests/Cache/ServerList.php` — same
- `app/Http/Libraries/Requests/Cache/ItemWeights.php` — same
- `app/Http/Libraries/Requests/Cache/Leaderboard.php` — same
- `app/Http/Libraries/Requests/Cache/v2/TerritoryList.php` — same

---

## Task 1: Add cache queue supervisor to Horizon

**Goal:** Give cache refresh jobs their own isolated Horizon supervisor so they don't compete with default queue workers.

**Files:**
- Modify: `config/horizon.php`

**Acceptance Criteria:**
- [ ] A `supervisor-cache` entry exists in `defaults` targeting the `cache` queue
- [ ] Both `production` and `local` environments reference `supervisor-cache`

**Verify:** `php artisan horizon:list` (or just confirm config parses) → `php artisan config:clear && php artisan tinker --execute="echo 'ok';"` exits 0

**Steps:**

- [ ] **Step 1: Add the supervisor to `defaults` in `config/horizon.php`**

Find the `'defaults'` array (around line 200) and add `supervisor-cache` alongside `supervisor-1`:

```php
'defaults' => [
    'supervisor-1' => [
        'connection' => 'redis',
        'queue' => ['default'],
        'balance' => 'auto',
        'autoScalingStrategy' => 'time',
        'maxProcesses' => 1,
        'maxTime' => 0,
        'maxJobs' => 0,
        'memory' => 128,
        'tries' => 1,
        'timeout' => 60,
        'nice' => 0,
    ],
    'supervisor-cache' => [
        'connection' => 'redis',
        'queue' => ['cache'],
        'balance' => 'auto',
        'autoScalingStrategy' => 'time',
        'maxProcesses' => 3,
        'maxTime' => 0,
        'maxJobs' => 0,
        'memory' => 128,
        'tries' => 1,
        'timeout' => 60,
        'nice' => 0,
    ],
],
```

- [ ] **Step 2: Reference `supervisor-cache` in both environment overrides**

Find the `'environments'` array and add `supervisor-cache` to `production` and `local`:

```php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'maxProcesses' => 10,
            'balanceMaxShift' => 1,
            'balanceCooldown' => 3,
        ],
        'supervisor-cache' => [
            'maxProcesses' => 5,
            'balanceMaxShift' => 1,
            'balanceCooldown' => 3,
        ],
    ],

    'local' => [
        'supervisor-1' => [
            'maxProcesses' => 3,
        ],
        'supervisor-cache' => [
            'maxProcesses' => 2,
        ],
    ],
],
```

- [ ] **Step 3: Verify config parses cleanly**

```bash
php artisan config:clear
php artisan tinker --execute="config('horizon.defaults');" 
```

Expected: array output showing both `supervisor-1` and `supervisor-cache`.

- [ ] **Step 4: Commit**

```bash
git add config/horizon.php
git commit -m "feat: add cache queue supervisor to Horizon config"
```

---

## Task 2: Create cache refresh jobs

**Goal:** Five queued jobs that each call their generator's `generate()` method, write the result and its SHA-512 hash to the cache store forever, and log + swallow failures to preserve stale data.

**Files:**
- Create: `app/Jobs/Cache/RefreshGuildListCache.php`
- Create: `app/Jobs/Cache/RefreshServerListCache.php`
- Create: `app/Jobs/Cache/RefreshItemWeightsCache.php`
- Create: `app/Jobs/Cache/RefreshLeaderboardCache.php`
- Create: `app/Jobs/Cache/RefreshTerritoryListCache.php`
- Create: `tests/Unit/Jobs/Cache/RefreshCacheJobsTest.php`

**Acceptance Criteria:**
- [ ] Each job writes `cache.{name}` and `cache.{name}.hash` to the cache store on success
- [ ] Each job logs the error and returns silently on failure (no throw)
- [ ] All jobs target the `cache` queue
- [ ] Unit tests confirm the write path and the failure path

**Verify:** `php artisan test tests/Unit/Jobs/Cache/RefreshCacheJobsTest.php` → all pass

**Steps:**

- [ ] **Step 1: Write failing unit tests first**

Create `tests/Unit/Jobs/Cache/RefreshCacheJobsTest.php`:

```php
<?php

namespace Tests\Unit\Jobs\Cache;

use App\Http\Libraries\Requests\Cache\GuildList;
use App\Http\Libraries\Requests\Cache\ServerList;
use App\Http\Libraries\Requests\Cache\ItemWeights;
use App\Http\Libraries\Requests\Cache\Leaderboard;
use App\Http\Libraries\Requests\Cache\v2\TerritoryList;
use App\Jobs\Cache\RefreshGuildListCache;
use App\Jobs\Cache\RefreshServerListCache;
use App\Jobs\Cache\RefreshItemWeightsCache;
use App\Jobs\Cache\RefreshLeaderboardCache;
use App\Jobs\Cache\RefreshTerritoryListCache;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RefreshCacheJobsTest extends TestCase
{
    public function test_refresh_guild_list_writes_data_and_hash(): void
    {
        $data = [['_id' => 'Wynntils', 'prefix' => 'WYN', 'color' => '#fff']];
        $this->mock(GuildList::class, fn ($m) => $m->shouldReceive('generate')->once()->andReturn($data));

        Cache::shouldReceive('forever')->once()->with('cache.guildList', $data);
        Cache::shouldReceive('forever')->once()->with('cache.guildList.hash', hash('sha512', serialize($data)));

        (new RefreshGuildListCache)->handle();
    }

    public function test_refresh_guild_list_logs_and_swallows_exception(): void
    {
        $this->mock(GuildList::class, fn ($m) => $m->shouldReceive('generate')->once()->andThrow(new \Exception('API down')));

        Cache::shouldReceive('forever')->never();
        Log::shouldReceive('error')->once()->with(\Mockery::pattern('/RefreshGuildListCache/'));

        (new RefreshGuildListCache)->handle();
    }

    public function test_refresh_server_list_writes_data_and_hash(): void
    {
        $data = ['servers' => ['WC1' => ['firstSeen' => 1000, 'players' => ['Player1']]]];
        $this->mock(ServerList::class, fn ($m) => $m->shouldReceive('generate')->once()->andReturn($data));

        Cache::shouldReceive('forever')->once()->with('cache.serverList', $data);
        Cache::shouldReceive('forever')->once()->with('cache.serverList.hash', hash('sha512', serialize($data)));

        (new RefreshServerListCache)->handle();
    }

    public function test_refresh_item_weights_writes_data_and_hash(): void
    {
        $data = ['wynnpool' => [], 'nori' => []];
        $this->mock(ItemWeights::class, fn ($m) => $m->shouldReceive('generate')->once()->andReturn($data));

        Cache::shouldReceive('forever')->once()->with('cache.itemWeights', $data);
        Cache::shouldReceive('forever')->once()->with('cache.itemWeights.hash', hash('sha512', serialize($data)));

        (new RefreshItemWeightsCache)->handle();
    }

    public function test_refresh_leaderboard_writes_data_and_hash(): void
    {
        $data = ['combatSolo' => ['1' => 'some-uuid']];
        $this->mock(Leaderboard::class, fn ($m) => $m->shouldReceive('generate')->once()->andReturn($data));

        Cache::shouldReceive('forever')->once()->with('cache.leaderboard', $data);
        Cache::shouldReceive('forever')->once()->with('cache.leaderboard.hash', hash('sha512', serialize($data)));

        (new RefreshLeaderboardCache)->handle();
    }

    public function test_refresh_territory_list_writes_data_and_hash(): void
    {
        $data = ['Detlas' => ['guild' => ['name' => 'Wynntils', 'prefix' => 'WYN', 'color' => '#fff'], 'acquired' => '2024-01-01T00:00:00Z', 'location' => ['start' => [0, 0], 'end' => [100, 100]]]];
        $this->mock(TerritoryList::class, fn ($m) => $m->shouldReceive('generate')->once()->andReturn($data));

        Cache::shouldReceive('forever')->once()->with('cache.territoryList', $data);
        Cache::shouldReceive('forever')->once()->with('cache.territoryList.hash', hash('sha512', serialize($data)));

        (new RefreshTerritoryListCache)->handle();
    }
}
```

- [ ] **Step 2: Run tests — confirm they fail with class-not-found**

```bash
php artisan test tests/Unit/Jobs/Cache/RefreshCacheJobsTest.php
```

Expected: error — `App\Jobs\Cache\RefreshGuildListCache not found`

- [ ] **Step 3: Create `app/Jobs/Cache/RefreshGuildListCache.php`**

```php
<?php

namespace App\Jobs\Cache;

use App\Http\Libraries\Requests\Cache\GuildList;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshGuildListCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('cache');
    }

    public function handle(): void
    {
        try {
            $data = (new GuildList)->generate();
            Cache::forever('cache.guildList', $data);
            Cache::forever('cache.guildList.hash', hash('sha512', serialize($data)));
        } catch (\Throwable $e) {
            Log::error('RefreshGuildListCache failed: ' . $e->getMessage());
        }
    }
}
```

- [ ] **Step 4: Create `app/Jobs/Cache/RefreshServerListCache.php`**

```php
<?php

namespace App\Jobs\Cache;

use App\Http\Libraries\Requests\Cache\ServerList;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshServerListCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('cache');
    }

    public function handle(): void
    {
        try {
            $data = (new ServerList)->generate();
            Cache::forever('cache.serverList', $data);
            Cache::forever('cache.serverList.hash', hash('sha512', serialize($data)));
        } catch (\Throwable $e) {
            Log::error('RefreshServerListCache failed: ' . $e->getMessage());
        }
    }
}
```

- [ ] **Step 5: Create `app/Jobs/Cache/RefreshItemWeightsCache.php`**

```php
<?php

namespace App\Jobs\Cache;

use App\Http\Libraries\Requests\Cache\ItemWeights;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshItemWeightsCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('cache');
    }

    public function handle(): void
    {
        try {
            $data = (new ItemWeights)->generate();
            Cache::forever('cache.itemWeights', $data);
            Cache::forever('cache.itemWeights.hash', hash('sha512', serialize($data)));
        } catch (\Throwable $e) {
            Log::error('RefreshItemWeightsCache failed: ' . $e->getMessage());
        }
    }
}
```

- [ ] **Step 6: Create `app/Jobs/Cache/RefreshLeaderboardCache.php`**

```php
<?php

namespace App\Jobs\Cache;

use App\Http\Libraries\Requests\Cache\Leaderboard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshLeaderboardCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('cache');
    }

    public function handle(): void
    {
        try {
            $data = (new Leaderboard)->generate();
            Cache::forever('cache.leaderboard', $data);
            Cache::forever('cache.leaderboard.hash', hash('sha512', serialize($data)));
        } catch (\Throwable $e) {
            Log::error('RefreshLeaderboardCache failed: ' . $e->getMessage());
        }
    }
}
```

- [ ] **Step 7: Create `app/Jobs/Cache/RefreshTerritoryListCache.php`**

```php
<?php

namespace App\Jobs\Cache;

use App\Http\Libraries\Requests\Cache\v2\TerritoryList;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshTerritoryListCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('cache');
    }

    public function handle(): void
    {
        try {
            $data = (new TerritoryList)->generate();
            Cache::forever('cache.territoryList', $data);
            Cache::forever('cache.territoryList.hash', hash('sha512', serialize($data)));
        } catch (\Throwable $e) {
            Log::error('RefreshTerritoryListCache failed: ' . $e->getMessage());
        }
    }
}
```

- [ ] **Step 8: Run tests — confirm they pass**

```bash
php artisan test tests/Unit/Jobs/Cache/RefreshCacheJobsTest.php
```

Expected: 6 tests, 6 assertions, all PASS

- [ ] **Step 9: Commit**

```bash
git add app/Jobs/Cache/ tests/Unit/Jobs/Cache/
git commit -m "feat: add cache refresh jobs with unit tests"
```

---

## Task 3: Register jobs in the scheduler

**Goal:** Each job runs on its own cadence via the Laravel scheduler so the cache is proactively warmed.

**Files:**
- Modify: `app/Console/Kernel.php`

**Acceptance Criteria:**
- [ ] All five jobs are scheduled with the correct intervals
- [ ] `->onOneServer()` is applied to each to prevent duplicate runs

**Verify:** `php artisan schedule:list` → shows all five jobs with correct intervals

**Steps:**

- [ ] **Step 1: Replace the empty schedule method in `app/Console/Kernel.php`**

```php
protected function schedule(Schedule $schedule)
{
    $schedule->job(new \App\Jobs\Cache\RefreshServerListCache)->everyThirtySeconds()->onOneServer();
    $schedule->job(new \App\Jobs\Cache\RefreshTerritoryListCache)->everyFifteenSeconds()->onOneServer();
    $schedule->job(new \App\Jobs\Cache\RefreshLeaderboardCache)->everyTenMinutes()->onOneServer();
    $schedule->job(new \App\Jobs\Cache\RefreshGuildListCache)->hourly()->onOneServer();
    $schedule->job(new \App\Jobs\Cache\RefreshItemWeightsCache)->hourly()->onOneServer();
}
```

Note: `everyFifteenSeconds()` and `everyThirtySeconds()` require Laravel 10+. Confirm with `php artisan --version`. If on Laravel 9, use `->cron('*/15 * * * * *')` and `->cron('*/30 * * * * *')` respectively.

- [ ] **Step 2: Verify**

```bash
php artisan schedule:list
```

Expected: table showing all five `App\Jobs\Cache\Refresh*` jobs with intervals `Every 15 seconds`, `Every 30 seconds`, `Every 10 minutes`, `Hourly`.

- [ ] **Step 3: Commit**

```bash
git add app/Console/Kernel.php
git commit -m "feat: schedule cache refresh jobs in Kernel"
```

---

## Task 4: Create HashesResource

**Goal:** A typed `JsonResource` for the hashes map that Scramble can read to generate accurate API docs.

**Files:**
- Create: `app/Http/Resources/Cache/HashesResource.php`

**Acceptance Criteria:**
- [ ] `toArray()` returns a typed shape matching `array{guildList: string|null, serverList: string|null, itemWeights: string|null, leaderboard: string|null, territoryList: string|null}`
- [ ] `$wrap` is null (no wrapping key)

**Verify:** `php artisan test` → no regressions

**Steps:**

- [ ] **Step 1: Create `app/Http/Resources/Cache/HashesResource.php`**

```php
<?php

namespace App\Http\Resources\Cache;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HashesResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array{guildList: string|null, serverList: string|null, itemWeights: string|null, leaderboard: string|null, territoryList: string|null}
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
```

- [ ] **Step 2: Verify no regressions**

```bash
php artisan test
```

Expected: all existing tests pass

- [ ] **Step 3: Commit**

```bash
git add app/Http/Resources/Cache/HashesResource.php
git commit -m "feat: add HashesResource for cache hashes endpoint"
```

---

## Task 5: Create cache controllers

**Goal:** Six invokable controllers — one per active cache endpoint plus `HashesController` — that read from the cache store and return typed resources with correct HTTP cache headers.

**Files:**
- Create: `app/Http/Controllers/Cache/GuildListController.php`
- Create: `app/Http/Controllers/Cache/ServerListController.php`
- Create: `app/Http/Controllers/Cache/ItemWeightsController.php`
- Create: `app/Http/Controllers/Cache/LeaderboardController.php`
- Create: `app/Http/Controllers/Cache/TerritoryListController.php`
- Create: `app/Http/Controllers/Cache/HashesController.php`
- Create: `tests/Feature/Cache/CacheControllersTest.php`

**Acceptance Criteria:**
- [ ] Each controller returns its typed resource with `timestamp`, `Cache-Control`, `Expires`, and `ETag` headers
- [ ] Cold start (null cache) triggers `dispatchSync` on the corresponding job
- [ ] `HashesController` returns a flat map of all five hashes
- [ ] Feature tests pass for all happy-path scenarios

**Verify:** `php artisan test tests/Feature/Cache/CacheControllersTest.php` → all pass

**Steps:**

- [ ] **Step 1: Write failing feature tests**

Create `tests/Feature/Cache/CacheControllersTest.php`:

```php
<?php

namespace Tests\Feature\Cache;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheControllersTest extends TestCase
{
    public function test_guild_list_returns_cached_data_with_headers(): void
    {
        $data = [['_id' => 'Wynntils', 'prefix' => 'WYN', 'color' => '#ffffff']];
        Cache::put('cache.guildList', $data);
        Cache::put('cache.guildList.hash', 'abc123hash');

        $response = $this->getJson('/cache/get/guildList');

        $response->assertStatus(200)->assertJson($data);
        $this->assertNotNull($response->headers->get('timestamp'));
        $this->assertStringContainsString('max-age=3600', $response->headers->get('Cache-Control'));
        $this->assertSame('"abc123hash"', $response->headers->get('ETag'));
    }

    public function test_server_list_returns_cached_data_with_headers(): void
    {
        $data = ['servers' => ['WC1' => ['firstSeen' => 1000000, 'players' => ['Player1']]]];
        Cache::put('cache.serverList', $data);
        Cache::put('cache.serverList.hash', 'serverhash');

        $response = $this->getJson('/cache/get/serverList');

        $response->assertStatus(200)->assertJson($data);
        $this->assertStringContainsString('max-age=30', $response->headers->get('Cache-Control'));
        $this->assertSame('"serverhash"', $response->headers->get('ETag'));
    }

    public function test_item_weights_returns_cached_data_with_headers(): void
    {
        $data = ['wynnpool' => ['Warchief Mask' => ['tank' => ['strengthPoints' => 1.0]]], 'nori' => []];
        Cache::put('cache.itemWeights', $data);
        Cache::put('cache.itemWeights.hash', 'weighthash');

        $response = $this->getJson('/cache/get/itemWeights');

        $response->assertStatus(200)->assertJson($data);
        $this->assertStringContainsString('max-age=3600', $response->headers->get('Cache-Control'));
    }

    public function test_leaderboard_returns_cached_data_with_headers(): void
    {
        $data = ['combatSolo' => ['1' => 'some-uuid', '2' => 'other-uuid']];
        Cache::put('cache.leaderboard', $data);
        Cache::put('cache.leaderboard.hash', 'lbhash');

        $response = $this->getJson('/cache/get/leaderboard');

        $response->assertStatus(200)->assertJson($data);
        $this->assertStringContainsString('max-age=600', $response->headers->get('Cache-Control'));
    }

    public function test_territory_list_returns_cached_data_with_headers(): void
    {
        $data = ['Detlas' => ['guild' => ['name' => 'Wynntils', 'prefix' => 'WYN', 'color' => '#fff'], 'acquired' => '2024-01-01T00:00:00Z', 'location' => ['start' => [0, 0], 'end' => [100, 100]]]];
        Cache::put('cache.territoryList', $data);
        Cache::put('cache.territoryList.hash', 'terrihash');

        $response = $this->getJson('/cache/get/territoryList');

        $response->assertStatus(200)->assertJson($data);
        $this->assertStringContainsString('max-age=15', $response->headers->get('Cache-Control'));
    }

    public function test_hashes_returns_flat_map(): void
    {
        Cache::put('cache.guildList.hash', 'gh');
        Cache::put('cache.serverList.hash', 'sh');
        Cache::put('cache.itemWeights.hash', 'iwh');
        Cache::put('cache.leaderboard.hash', 'lbh');
        Cache::put('cache.territoryList.hash', 'tlh');

        $response = $this->getJson('/cache/getHashes');

        $response->assertStatus(200)->assertExactJson([
            'guildList'     => 'gh',
            'serverList'    => 'sh',
            'itemWeights'   => 'iwh',
            'leaderboard'   => 'lbh',
            'territoryList' => 'tlh',
        ]);
    }
}
```

- [ ] **Step 2: Run tests — confirm they fail with 404 (routes not registered yet)**

```bash
php artisan test tests/Feature/Cache/CacheControllersTest.php
```

Expected: FAIL — 404 responses (controllers don't exist yet)

- [ ] **Step 3: Create `app/Http/Controllers/Cache/GuildListController.php`**

```php
<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\GuildListCacheResource;
use App\Jobs\Cache\RefreshGuildListCache;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class GuildListController extends Controller
{
    /**
     * Get the guild list
     */
    public function __invoke(): GuildListCacheResource|JsonResponse
    {
        $data = Cache::get('cache.guildList');

        if ($data === null) {
            RefreshGuildListCache::dispatchSync();
            $data = Cache::get('cache.guildList');
        }

        return (new GuildListCacheResource($data))
            ->response()
            ->header('timestamp', currentTimeMillis())
            ->setCache(['max_age' => 3600, 's_maxage' => 3600, 'public' => true])
            ->setExpires(now()->addSeconds(3600))
            ->setEtag(Cache::get('cache.guildList.hash'));
    }
}
```

- [ ] **Step 4: Create `app/Http/Controllers/Cache/ServerListController.php`**

```php
<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\ServerListCacheResource;
use App\Jobs\Cache\RefreshServerListCache;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class ServerListController extends Controller
{
    /**
     * Get the server list
     */
    public function __invoke(): ServerListCacheResource|JsonResponse
    {
        $data = Cache::get('cache.serverList');

        if ($data === null) {
            RefreshServerListCache::dispatchSync();
            $data = Cache::get('cache.serverList');
        }

        return (new ServerListCacheResource($data))
            ->response()
            ->header('timestamp', currentTimeMillis())
            ->setCache(['max_age' => 30, 's_maxage' => 30, 'public' => true])
            ->setExpires(now()->addSeconds(30))
            ->setEtag(Cache::get('cache.serverList.hash'));
    }
}
```

- [ ] **Step 5: Create `app/Http/Controllers/Cache/ItemWeightsController.php`**

```php
<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\ItemWeightsCacheResource;
use App\Jobs\Cache\RefreshItemWeightsCache;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class ItemWeightsController extends Controller
{
    /**
     * Get item build weights
     */
    public function __invoke(): ItemWeightsCacheResource|JsonResponse
    {
        $data = Cache::get('cache.itemWeights');

        if ($data === null) {
            RefreshItemWeightsCache::dispatchSync();
            $data = Cache::get('cache.itemWeights');
        }

        return (new ItemWeightsCacheResource($data))
            ->response()
            ->header('timestamp', currentTimeMillis())
            ->setCache(['max_age' => 3600, 's_maxage' => 3600, 'public' => true])
            ->setExpires(now()->addSeconds(3600))
            ->setEtag(Cache::get('cache.itemWeights.hash'));
    }
}
```

- [ ] **Step 6: Create `app/Http/Controllers/Cache/LeaderboardController.php`**

```php
<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\LeaderboardCacheResource;
use App\Jobs\Cache\RefreshLeaderboardCache;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class LeaderboardController extends Controller
{
    /**
     * Get the leaderboard
     */
    public function __invoke(): LeaderboardCacheResource|JsonResponse
    {
        $data = Cache::get('cache.leaderboard');

        if ($data === null) {
            RefreshLeaderboardCache::dispatchSync();
            $data = Cache::get('cache.leaderboard');
        }

        return (new LeaderboardCacheResource($data))
            ->response()
            ->header('timestamp', currentTimeMillis())
            ->setCache(['max_age' => 600, 's_maxage' => 600, 'public' => true])
            ->setExpires(now()->addSeconds(600))
            ->setEtag(Cache::get('cache.leaderboard.hash'));
    }
}
```

- [ ] **Step 7: Create `app/Http/Controllers/Cache/TerritoryListController.php`**

```php
<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\TerritoryListCacheResource;
use App\Jobs\Cache\RefreshTerritoryListCache;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class TerritoryListController extends Controller
{
    /**
     * Get the territory list
     */
    public function __invoke(): TerritoryListCacheResource|JsonResponse
    {
        $data = Cache::get('cache.territoryList');

        if ($data === null) {
            RefreshTerritoryListCache::dispatchSync();
            $data = Cache::get('cache.territoryList');
        }

        return (new TerritoryListCacheResource($data))
            ->response()
            ->header('timestamp', currentTimeMillis())
            ->setCache(['max_age' => 15, 's_maxage' => 15, 'public' => true])
            ->setExpires(now()->addSeconds(15))
            ->setEtag(Cache::get('cache.territoryList.hash'));
    }
}
```

- [ ] **Step 8: Create `app/Http/Controllers/Cache/HashesController.php`**

```php
<?php

namespace App\Http\Controllers\Cache;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cache\HashesResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Cache;

#[Group('Cache')]
class HashesController extends Controller
{
    /**
     * Get hashes for all active caches
     */
    public function __invoke(): HashesResource
    {
        return new HashesResource([
            'guildList'     => Cache::get('cache.guildList.hash'),
            'serverList'    => Cache::get('cache.serverList.hash'),
            'itemWeights'   => Cache::get('cache.itemWeights.hash'),
            'leaderboard'   => Cache::get('cache.leaderboard.hash'),
            'territoryList' => Cache::get('cache.territoryList.hash'),
        ]);
    }
}
```

- [ ] **Step 9: Run tests — confirm they still fail (routes not wired)**

```bash
php artisan test tests/Feature/Cache/CacheControllersTest.php
```

Expected: FAIL — 404 (routes not updated yet — Task 6 does that)

- [ ] **Step 10: Commit**

```bash
git add app/Http/Controllers/Cache/ tests/Feature/Cache/
git commit -m "feat: add invokable cache controllers and feature tests"
```

---

## Task 6: Update routes

**Goal:** Replace the old `CacheController`-based routes with the new invokable controllers. Remove the v2 route file.

**Files:**
- Modify: `routes/api/cache.php`
- Delete: `routes/api/v2/cache.php`

**Acceptance Criteria:**
- [ ] `GET /cache/get/guildList`, `/cache/get/serverList`, `/cache/get/itemWeights`, `/cache/get/leaderboard`, `/cache/get/territoryList`, `/cache/getHashes` all resolve to the new controllers
- [ ] The old generic catch-all `get/{cache}` routes are gone
- [ ] `routes/api/v2/cache.php` is deleted
- [ ] Feature tests pass

**Verify:** `php artisan test tests/Feature/Cache/CacheControllersTest.php` → all pass

**Steps:**

- [ ] **Step 1: Replace `routes/api/cache.php` entirely**

```php
<?php

use App\Http\Controllers\Cache\GuildListController;
use App\Http\Controllers\Cache\HashesController;
use App\Http\Controllers\Cache\ItemWeightsController;
use App\Http\Controllers\Cache\LeaderboardController;
use App\Http\Controllers\Cache\ServerListController;
use App\Http\Controllers\Cache\TerritoryListController;

Route::get('get/guildList', GuildListController::class)->name('cache.guildList');
Route::get('get/serverList', ServerListController::class)->name('cache.serverList');
Route::get('get/itemWeights', ItemWeightsController::class)->name('cache.itemWeights');
Route::get('get/leaderboard', LeaderboardController::class)->name('cache.leaderboard');
Route::get('get/territoryList', TerritoryListController::class)->name('cache.territoryList');
Route::get('getHashes', HashesController::class)->name('cache.getHashes');
```

- [ ] **Step 2: Delete `routes/api/v2/cache.php`**

```bash
rm routes/api/v2/cache.php
```

If `routes/api/v2/` is now empty, remove it too:
```bash
rmdir routes/api/v2/ 2>/dev/null || true
```

- [ ] **Step 3: Run feature tests**

```bash
php artisan test tests/Feature/Cache/CacheControllersTest.php
```

Expected: all 6 tests PASS

- [ ] **Step 4: Run full suite to check no regressions**

```bash
php artisan test
```

Expected: all tests pass

- [ ] **Step 5: Commit**

```bash
git add routes/api/cache.php
git rm routes/api/v2/cache.php
git commit -m "feat: wire cache routes to invokable controllers, remove v2 cache routes"
```

---

## Task 7: Update AuthController

**Goal:** Replace the `CacheManager::getHashes()` call in `AuthController` with direct `Cache::get()` reads so the auth response continues to include all five cache hashes without depending on `CacheManager`.

**Files:**
- Modify: `app/Http/Controllers/AuthController.php`

**Acceptance Criteria:**
- [ ] `CacheManager` is no longer imported or used in `AuthController`
- [ ] The `hashes` key in the auth response contains a flat `string|null` map for all five active caches

**Verify:** `php artisan test` → all pass (existing auth tests should cover the response shape)

**Steps:**

- [ ] **Step 1: Open `app/Http/Controllers/AuthController.php`**

Find the import:
```php
use App\Http\Libraries\CacheManager;
```
And the usage:
```php
$response['hashes'] = CacheManager::getHashes();
```

- [ ] **Step 2: Remove the `CacheManager` import and add `Cache` facade import**

Remove:
```php
use App\Http\Libraries\CacheManager;
```

Add (if not already present):
```php
use Illuminate\Support\Facades\Cache;
```

- [ ] **Step 3: Replace the `getHashes()` call**

Replace:
```php
$response['hashes'] = CacheManager::getHashes();
```

With:
```php
$response['hashes'] = [
    'guildList'     => Cache::get('cache.guildList.hash'),
    'serverList'    => Cache::get('cache.serverList.hash'),
    'itemWeights'   => Cache::get('cache.itemWeights.hash'),
    'leaderboard'   => Cache::get('cache.leaderboard.hash'),
    'territoryList' => Cache::get('cache.territoryList.hash'),
];
```

- [ ] **Step 4: Run tests**

```bash
php artisan test
```

Expected: all pass

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/AuthController.php
git commit -m "fix: replace CacheManager::getHashes() with direct cache reads in AuthController"
```

---

## Task 8: Delete deprecated code and clean up generators

**Goal:** Remove all code that is no longer referenced — deprecated generators, `CacheManager`, `CacheContract`, old `CacheController` — and strip `implements CacheContract` and `refreshRate()` from the five active generators since they now only need `generate()`.

**Files:**
- Delete: `app/Http/Libraries/CacheManager.php`
- Delete: `app/Http/Libraries/Requests/Cache/CacheContract.php`
- Delete: `app/Http/Libraries/Requests/Cache/GatheringSpots.php`
- Delete: `app/Http/Libraries/Requests/Cache/IngredientList.php`
- Delete: `app/Http/Libraries/Requests/Cache/ItemList.php`
- Delete: `app/Http/Libraries/Requests/Cache/MapLocations.php`
- Delete: `app/Http/Libraries\Requests\Cache\TerritoryList.php` (v1, not v2)
- Delete: `app/Http/Libraries/Requests/Cache/GuildListWithColors.php`
- Delete: `app/Http/Controllers/CacheController.php`
- Modify: `app/Http/Libraries/Requests/Cache/GuildList.php`
- Modify: `app/Http/Libraries/Requests/Cache/ServerList.php`
- Modify: `app/Http/Libraries/Requests/Cache/ItemWeights.php`
- Modify: `app/Http/Libraries/Requests/Cache/Leaderboard.php`
- Modify: `app/Http/Libraries/Requests/Cache/v2/TerritoryList.php`

**Acceptance Criteria:**
- [ ] No remaining references to `CacheManager` or `CacheContract` anywhere in `app/`
- [ ] Active generators no longer implement `CacheContract` or declare `refreshRate()`
- [ ] All tests pass after deletion

**Verify:** `php artisan test` → all pass; `grep -r "CacheManager\|CacheContract" app/` → no output

**Steps:**

- [ ] **Step 1: Delete deprecated files**

```bash
git rm app/Http/Libraries/CacheManager.php
git rm app/Http/Libraries/Requests/Cache/CacheContract.php
git rm app/Http/Libraries/Requests/Cache/GatheringSpots.php
git rm app/Http/Libraries/Requests/Cache/IngredientList.php
git rm app/Http/Libraries/Requests/Cache/ItemList.php
git rm app/Http/Libraries/Requests/Cache/MapLocations.php
git rm app/Http/Libraries/Requests/Cache/TerritoryList.php
git rm app/Http/Libraries/Requests/Cache/GuildListWithColors.php
git rm app/Http/Controllers/CacheController.php
```

- [ ] **Step 2: Strip `CacheContract` from `GuildList.php`**

In `app/Http/Libraries/Requests/Cache/GuildList.php`:

Remove the `use` statement (if present) and change the class declaration from:
```php
class GuildList implements CacheContract
```
to:
```php
class GuildList
```

Remove the `refreshRate()` method entirely.

- [ ] **Step 3: Strip `CacheContract` from `ServerList.php`**

In `app/Http/Libraries/Requests/Cache/ServerList.php`:

Change:
```php
class ServerList implements CacheContract
```
to:
```php
class ServerList
```

Remove the `refreshRate()` method entirely.

- [ ] **Step 4: Strip `CacheContract` from `ItemWeights.php`**

In `app/Http/Libraries/Requests/Cache/ItemWeights.php`:

Change:
```php
class ItemWeights implements CacheContract
```
to:
```php
class ItemWeights
```

Remove the `refreshRate()` method entirely.

- [ ] **Step 5: Strip `CacheContract` from `Leaderboard.php`**

In `app/Http/Libraries/Requests/Cache/Leaderboard.php`:

Change:
```php
class Leaderboard implements CacheContract
```
to:
```php
class Leaderboard
```

Remove the `refreshRate()` method entirely.

- [ ] **Step 6: Strip `CacheContract` from `v2/TerritoryList.php`**

In `app/Http/Libraries/Requests/Cache/v2/TerritoryList.php`:

Remove the import:
```php
use App\Http\Libraries\Requests\Cache\CacheContract;
```

Change:
```php
class TerritoryList implements CacheContract
```
to:
```php
class TerritoryList
```

Remove the `refreshRate()` method entirely.

- [ ] **Step 7: Verify no remaining references**

```bash
grep -r "CacheManager\|CacheContract" app/
```

Expected: no output

- [ ] **Step 8: Run full test suite**

```bash
php artisan test
```

Expected: all tests pass

- [ ] **Step 9: Commit**

```bash
git add app/Http/Libraries/Requests/Cache/
git commit -m "chore: delete deprecated cache generators, CacheManager, CacheContract, and old CacheController"
```
