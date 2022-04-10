<?php

use App\Http\Controllers\PatreonController;

Route::controller(PatreonController::class)->group(static function () {
    Route::post('webhook', 'webhook');
    Route::get('test', 'test')->middleware('auth:api');
});
