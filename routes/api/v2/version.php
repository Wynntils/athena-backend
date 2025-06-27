<?php

use App\Http\Controllers\VersionController;

Route::controller(VersionController::class)->group(static function () {
    Route::get('changelog/{version1}/{version2}', [VersionController::class, 'changelogBetweenV2'])->name('changelogBetweenV2');
});
