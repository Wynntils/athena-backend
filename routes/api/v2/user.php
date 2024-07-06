<?php

use App\Http\Controllers\UserController;

Route::controller(UserController::class)->group(static function () {
    Route::post('getInfo', 'getInfoV2')
        ->middleware('block.user.agents')
        ->name('getInfoV2');
});
