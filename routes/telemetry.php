<?php

use App\Http\Controllers\TelemeteryController;

Route::controller(TelemeteryController::class)->middleware('auth:token')->group(static function () {
    Route::post('sendGatheringSpot', 'sendGatheringSpot');
});
