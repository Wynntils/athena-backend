<?php

namespace App\Jobs\Cache;

use App\Http\Libraries\Requests\Cache\Leaderboard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RefreshLeaderboardCache implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('cache');
    }

    public function handle(): void
    {
        try {
            $data = app(Leaderboard::class)->generate();
            Cache::forever('cache.leaderboard', $data);
            Cache::forever('cache.leaderboard.hash', hash('sha512', serialize($data)));
        } catch (\Throwable $e) {
            Log::error('RefreshLeaderboardCache failed', ['exception' => $e]);
        }
    }
}
