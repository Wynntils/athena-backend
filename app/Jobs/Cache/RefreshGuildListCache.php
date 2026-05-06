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
            $data = app(GuildList::class)->generate();
            Cache::forever('cache.guildList', $data);
            Cache::forever('cache.guildList.hash', hash('sha512', serialize($data)));
        } catch (\Throwable $e) {
            Log::error('RefreshGuildListCache failed: ' . $e->getMessage());
        }
    }
}
