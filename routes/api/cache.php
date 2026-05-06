<?php

use App\Http\Controllers\CacheController;

Route::controller(CacheController::class)->group(static function() {
    Route::get('get/guildList', 'getGuildList')->name('cache.guildList');
    Route::get('get/serverList', 'getServerList')->name('cache.serverList');
    Route::get('get/itemWeights', 'getItemWeights')->name('cache.itemWeights');
    Route::get('get/leaderboard', 'getLeaderboard')->name('cache.leaderboard');
    Route::get('get/{cache}', 'getCache')->name('getCache');
    Route::get('getHashes', 'getHashes')->name('getHashes');
});
