<?php

use App\Http\Controllers\TelemeteryController;

Route::controller(TelemeteryController::class)->prefix('telemetry')->group(static function () {
    Route::post('sendGatheringSpot', 'sendGatheringSpot');
});
