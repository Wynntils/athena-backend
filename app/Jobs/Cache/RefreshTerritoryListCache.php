<?php

namespace App\Jobs\Cache;

use App\Http\Libraries\Requests\Cache\v2\TerritoryList;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshTerritoryListCache implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('cache');
    }

    public function handle(): void
    {
        try {
            $data = app(TerritoryList::class)->generate();
            Cache::forever('cache.territoryList', $data);
            Cache::forever('cache.territoryList.hash', hash('sha512', serialize($data)));
        } catch (\Throwable $e) {
            Log::error('RefreshTerritoryListCache failed', ['exception' => $e]);
        }
    }
}
