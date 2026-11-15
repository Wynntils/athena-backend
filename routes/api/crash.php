<?php

use App\Http\Controllers\CrashReportController;

Route::post('report', [CrashReportController::class, 'report'])->middleware('auth:token')->name('report');
