<?php

use App\Http\Controllers\CapeController;

Route::controller(CapeController::class)->group(static function () {
    Route::get('get/{id}', 'get');
    Route::get('user/{uuid}', 'user');
    Route::get('list', 'list');
    Route::post('delete/{token}', 'delete');
    Route::prefix('queue')->group(function () {
        Route::get('get/{id}', 'getQueue');
        Route::get('list', 'listQueue');
        Route::get('approve/{token}/{sha}', 'approve');
        Route::get('ban/{token}/{sha}', 'ban');
        Route::post('upload/{token}', 'upload');
    });
});

