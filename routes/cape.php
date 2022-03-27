<?php

use App\Http\Controllers\CapeController;

Route::controller(CapeController::class)->group(static function () {
    Route::get('get/{id}', 'getCape');
    Route::get('user/{uuid}', 'getUserCape');
    Route::get('list', 'list');
    Route::post('delete/{token}', 'delete')->middleware('cape.token');
    Route::prefix('queue')->group(function () {
        Route::get('get/{id}', 'queueGetCape');
        Route::get('list', 'queueList');
        Route::get('approve/{token}/{sha}', 'approveCape')->middleware('cape.token');
        Route::get('ban/{token}/{sha}', 'banCape')->middleware('cape.token');
        Route::post('upload/{token}', 'uploadCape')->middleware('cape.token');
    });
});

