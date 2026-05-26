<?php

namespace App\Jobs\Cache;

use App\Http\Libraries\Requests\Cache\WorldEvents;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshWorldEventsCache implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('cache');
    }

    public function handle(): void
    {
        try {
            $data = app(WorldEvents::class)->generate();
            Cache::forever('cache.worldEvents', $data);
            Cache::forever('cache.worldEvents.hash', hash('sha512', serialize($data)));
        } catch (\Throwable $e) {
            Log::error('RefreshWorldEventsCache failed', ['exception' => $e]);
        }
    }
}
