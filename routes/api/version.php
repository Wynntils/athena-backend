<?php

use App\Http\Controllers\VersionController;

Route::get('latest/{stream}', [VersionController::class, 'latest'])->name('latest');
Route::get('changelog/{version}', [VersionController::class, 'changelog'])->name('changelog');
Route::get('download/{version}/{stream}/{modloader?}', [VersionController::class, 'download'])->where('stream', 're|ce')->name('download');
Route::get('changelog/{version1}/{version2}', [VersionController::class, 'changelogBetween'])->name('changelogBetween');
