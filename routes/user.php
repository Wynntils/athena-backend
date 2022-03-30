<?php

use App\Http\Controllers\UserController;

Route::controller(UserController::class)->group(static function () {
    Route::middleware(['auth:token'])->group(static function () {
        Route::get('getConfigs', 'getConfigs');
        Route::post('uploadConfigs', 'uploadConfigs');
        Route::post('updateDiscord', 'updateDiscord');
    });
//    Route::post('setUserPassword', 'setUserPassword');
    Route::get('getInfo/{user}', 'getInfo');

    Route::post('getInfo', 'getInfoLegacy');
});
