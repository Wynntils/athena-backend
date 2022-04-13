<?php

use App\Http\Controllers\TelemetryController;

Route::controller(TelemetryController::class)->middleware('auth:token')->group(static function () {
    Route::post('sendGatheringSpot', 'sendGatheringSpot');
});
