<?php

use App\Http\Controllers\CacheController;

Route::controller(CacheController::class)->group(static function() {
    Route::get('get/{cache}', 'getCache');
    Route::get('getHashes', 'getHashes');
});
