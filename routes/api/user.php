<?php

use App\Http\Controllers\UserController;

Route::controller(UserController::class)->group(static function () {
    // Game mod routes — authenticated via authToken header
    Route::middleware(['auth:token'])->group(static function () {
        Route::get('getConfigs', 'getConfigs')->name('getConfigs');
        Route::post('uploadConfigs', 'uploadConfigs')->name('uploadConfigs');
        Route::post('updateDiscord', 'updateDiscord')->name('updateDiscord');
    });

    // Web routes — authenticated via authToken header or session cookie (Sanctum SPA)
    Route::middleware(['auth:token,sanctum'])->group(static function () {
        Route::post('cape/upload', 'uploadCapeWeb')->name('cape.upload');
        Route::post('cape/select', 'selectCape')->name('cape.select');
        Route::get('cape/elytra-mode', 'getElytraMode')->name('cape.elytraMode.get');
        Route::post('cape/elytra-mode', 'setElytraMode')->name('cape.elytraMode.set');
    });

//    Route::post('setUserPassword', 'setUserPassword');

    Route::middleware(['block.user.agents'])->group(static function () {
        Route::get('getInfo/{user}', 'getInfo')->name('getInfo');
        Route::post('getInfo', 'getInfoPost')->name('getInfoLegacy'); // Legacy route
    });

});
