<?php


use App\Http\Controllers\CacheController;

Route::controller(CacheController::class)->group(static function () {
    Route::get('get/territoryList', 'getTerritoryList')->name('v2.cache.territoryList');
    Route::get('get/{cache}', 'getCacheV2')->name('v2.cache.get');
});
