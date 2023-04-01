<?php

use App\Http\Controllers\CrashReportController;

Route::post('report', [CrashReportController::class, 'report'])->name('report');
