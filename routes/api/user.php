<?php

use App\Http\Controllers\UserController;

Route::controller(UserController::class)->group(static function () {
    Route::middleware(['auth:token'])->group(static function () {
        Route::get('getConfigs', 'getConfigs')->name('getConfigs');
        Route::post('uploadConfigs', 'uploadConfigs')->name('uploadConfigs');
        Route::post('updateDiscord', 'updateDiscord')->name('updateDiscord');
    });
//    Route::post('setUserPassword', 'setUserPassword');
    Route::get('getInfo/{user}', 'getInfo')->name('getInfo');

    Route::post('getInfo', 'getInfoPost')->name('getInfoLegacy');

});

// v2 routes

