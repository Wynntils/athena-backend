<?php

use App\Http\Controllers\UserController;

Route::controller(UserController::class)->group(static function () {
    Route::middleware(['auth:token'])->group(static function () {
        Route::get('getConfigs', 'getConfigs')->name('getConfigs');
        Route::post('uploadConfigs', 'uploadConfigs')->name('uploadConfigs');
        Route::post('updateDiscord', 'updateDiscord')->name('updateDiscord');
        Route::post('cape/upload', 'uploadCapeWeb')->name('cape.upload');
        Route::post('cape/select', 'selectCape')->name('cape.select');
    });
//    Route::post('setUserPassword', 'setUserPassword');

    Route::middleware(['block.user.agents'])->group(static function () {
        Route::get('getInfo/{user}', 'getInfo')->name('getInfo');
        Route::post('getInfo', 'getInfoPost')->name('getInfoLegacy'); // Legacy route
    });

});
