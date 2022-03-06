<?php

use App\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->controller(UserController::class)->group(static function () {
    Route::post('updateDiscord', 'updateDiscord');
    Route::post('uploadConfigs', 'uploadConfigs');
    Route::post('setUserPassword', 'setUserPassword');
    Route::post('getInfo', 'getInfo');
});
