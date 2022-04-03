<?php

use App\Http\Controllers\PatreonController;

Route::controller(PatreonController::class)->group(static function () {
    Route::post('webhook', 'webhook');
});
