<?php

use App\Http\Controllers\CapeController;

Route::controller(CapeController::class)->group(static function () {
    Route::get('get/{id}', 'getCape');
    Route::get('user/{uuid}', 'getUserCape');
    Route::get('list', 'list');
    Route::post('delete/{token}', 'delete');
    Route::prefix('queue')->group(function () {
        Route::get('get/{id}', 'queueGetCape');
        Route::get('list', 'queueList');
        Route::get('approve/{token}/{sha}', 'approveCape');
        Route::get('ban/{token}/{sha}', 'banCape');
        Route::post('upload/{token}', 'uploadCape');
    });
});

