<?php

use App\Http\Controllers\VersionController;

Route::get('latest/{stream}', [VersionController::class, 'latest'])->name('latest');
Route::get('changelog/{version}', [VersionController::class, 'changelog'])->name('changelog');
Route::get('download/{version}/{stream}/{modloader?}', [VersionController::class, 'download'])->name('download');
