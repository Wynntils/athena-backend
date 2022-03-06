<?php

use App\Http\Controllers\CacheController;

Route::controller(CacheController::class)->group(static function() {
    Route::get('get/{name}', 'get');
    Route::get('getHashes', 'getHashes');
});
