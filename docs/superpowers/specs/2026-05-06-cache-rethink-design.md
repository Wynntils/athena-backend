# Cache System Rethink

**Date:** 2026-05-06
**Status:** Approved

## Overview

Replace the current on-request lazy cache system with scheduled queued jobs that proactively warm the cache on a fixed interval. Controllers become thin readers that fetch pre-built data from the cache store. The result is a proper Scramble-compatible resource per endpoint, faster response times, and a significantly simplified architecture.

## What Gets Deleted

The following are removed entirely with no backwards-compatibility stubs:

**Generators (deprecated):**
- `App\Http\Libraries\Requests\Cache\GatheringSpots`
- `App\Http\Libraries\Requests\Cache\IngredientList`
- `App\Http\Libraries\Requests\Cache\ItemList`
- `App\Http\Libraries\Requests\Cache\MapLocations`
- `App\Http\Libraries\Requests\Cache\TerritoryList` (v1)
- `App\Http\Libraries\Requests\Cache\GuildListWithColors`

**Infrastructure:**
- `App\Http\Libraries\CacheManager`
- `App\Http\Libraries\Requests\Cache\CacheContract`
- `App\Http\Controllers\CacheController`
- `App\Http\Resources\Cache\GuildListWithColorsCacheResource` (if it exists)

**Routes:**
- Generic catch-all `GET cache/get/{cache}` and `GET v2/cache/get/{cache}`
- `GET cache/getHashes`
- The entire `routes/api/v2/cache.php` route file

The v1/v2 versioning split is eliminated. `territoryList` becomes a flat named endpoint.

## Cache Jobs

Five jobs in `App\Jobs\Cache\`, each implementing `ShouldQueue` and running on a dedicated `cache` queue.

| Job | Cache key | Schedule |
|-----|-----------|----------|
| `RefreshGuildListCache` | `cache.guildList` | Every 60 minutes |
| `RefreshServerListCache` | `cache.serverList` | Every 30 seconds |
| `RefreshItemWeightsCache` | `cache.itemWeights` | Every 60 minutes |
| `RefreshLeaderboardCache` | `cache.leaderboard` | Every 10 minutes |
| `RefreshTerritoryListCache` | `cache.territoryList` | Every 15 seconds |

Each job:
1. Instantiates its corresponding generator class and calls `generate()`
2. On success: writes data with `Cache::forever('cache.{name}', $data)` and stores `Cache::forever('cache.{name}.hash', hash('sha512', serialize($data)))`
3. On failure: logs the exception and returns — the existing cached value is left intact (stale data serves until the next successful run)

The `refreshRate()` method on generators is removed along with `CacheContract` — the schedule in `Kernel.php` owns the interval.

Generators keep their existing transformation logic unchanged. They are moved to remain in `App\Http\Libraries\Requests\Cache\` but the interface they previously implemented is gone.

Jobs are scheduled in `App\Console\Kernel.php`:

```php
$schedule->job(new RefreshServerListCache)->everyThirtySeconds()->onOneServer();
$schedule->job(new RefreshTerritoryListCache)->everyFifteenSeconds()->onOneServer();
$schedule->job(new RefreshLeaderboardCache)->everyTenMinutes()->onOneServer();
$schedule->job(new RefreshGuildListCache)->hourly()->onOneServer();
$schedule->job(new RefreshItemWeightsCache)->hourly()->onOneServer();
```

`->onOneServer()` prevents duplicate runs when multiple scheduler processes are running.

## Controllers

The monolithic `CacheController` is replaced by five invokable controllers in `App\Http\Controllers\Cache\`:

| Controller | Route |
|---|---|
| `GuildListController` | `GET /cache/get/guildList` |
| `ServerListController` | `GET /cache/get/serverList` |
| `ItemWeightsController` | `GET /cache/get/itemWeights` |
| `LeaderboardController` | `GET /cache/get/leaderboard` |
| `TerritoryListController` | `GET /cache/get/territoryList` |

Each controller's `__invoke` method:
1. Reads `Cache::get('cache.{name}')` from the store
2. **Cold start fallback:** if null, calls `RefreshXxxCache::dispatchSync()` then re-reads the cache
3. Returns the typed `JsonResource` with `Cache-Control`, `Expires`, and `ETag` headers set from the stored hash

Response headers (same behaviour as current `serveCache()`):
- `timestamp`: current time in milliseconds
- `Cache-Control: public, max-age={interval}, s-maxage={interval}`
- `Expires`: now + interval
- `ETag`: `cache.{name}.hash` value from cache store

Each controller carries `#[Group('Cache')]` and declares its return type as `XxxCacheResource|JsonResponse` so Scramble infers the response shape correctly — no `#[ExcludeRouteFromDocs]` needed.

## Resources

The five existing resources in `App\Http\Resources\Cache\` are unchanged:

- `GuildListCacheResource`
- `ServerListCacheResource`
- `ItemWeightsCacheResource`
- `LeaderboardCacheResource`
- `TerritoryListCacheResource`

Each has a typed `toArray()` return signature already. No new resources are required.

## Final Structure

```
App\Http\Controllers\Cache\
    GuildListController
    ServerListController
    ItemWeightsController
    LeaderboardController
    TerritoryListController

App\Jobs\Cache\
    RefreshGuildListCache
    RefreshServerListCache
    RefreshItemWeightsCache
    RefreshLeaderboardCache
    RefreshTerritoryListCache

App\Http\Libraries\Requests\Cache\   (generators, kept, CacheContract removed)
    GuildList
    ServerList
    ItemWeights
    Leaderboard
    v2/TerritoryList

App\Http\Resources\Cache\            (unchanged)
    GuildListCacheResource
    ServerListCacheResource
    ItemWeightsCacheResource
    LeaderboardCacheResource
    TerritoryListCacheResource

routes/api/cache.php                 (updated, catch-alls and hashes route removed)
routes/api/v2/cache.php              (deleted)
App\Console\Kernel.php               (schedules added)
```

## Error Handling

- Job failures are logged via `Log::error()` with the exception message
- Stale cache is served transparently — no error message is injected into the response payload
- Cold start (null cache): `dispatchSync()` runs the job inline; if that also fails, the exception propagates and Laravel returns a 500

## Horizon Configuration

Jobs run on a dedicated `cache` queue. Horizon config should define this queue with appropriate worker counts given the high-frequency jobs (`serverList` at 30s, `territoryList` at 15s).
