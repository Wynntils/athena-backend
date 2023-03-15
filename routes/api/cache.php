<?php

use App\Http\Controllers\CacheController;

Route::controller(CacheController::class)->group(static function() {
    Route::get('get/{cache}', 'getCache')->name('getCache');
    Route::get('getHashes', 'getHashes')->name('getHashes');
});
