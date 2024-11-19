<?php

namespace App\Jobs;

use App\Http\Libraries\Requests\Cache\CacheContract;
use App\Managers\CacheManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $cacheName;

    public function __construct(string $cacheName)
    {
        $this->cacheName = $cacheName;
    }

    public function handle()
    {
        $cacheClass = CacheManager::getCacheClass($this->cacheName);

        if (!$cacheClass instanceof CacheContract) {
            Log::error("Invalid cache class for {$this->cacheName}");
            return;
        }

        try {
            Log::info("Generating cache for {$this->cacheName}");
            $data = $cacheClass->generate();
            Cache::forever("{$this->cacheName}.data", $data);
            Cache::forever("{$this->cacheName}.hash", md5(serialize($data)));
        } catch (\Exception $e) {
            Log::error("Cache generation failed for {$this->cacheName}: {$e->getMessage()}");
            // Retain old cache on failure
        }
    }
}
