<?php

use App\Http\Controllers\TelemeteryController;

Route::controller(TelemeteryController::class)->group(static function () {
    Route::post('sendGatheringSpot', 'sendGatheringSpot');
});
