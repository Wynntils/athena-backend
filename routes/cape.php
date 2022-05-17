<?php

use App\Http\Controllers\CapeController;

Route::controller(CapeController::class)->group(static function () {
    Route::get('get/{id}', 'getCape');
    Route::get('user/{uuid}', 'getUserCape');
    Route::get('list', 'list');
    Route::get('ban/{token}/{sha}', 'banCape')->middleware('cape.token')->name('cape.ban');
    Route::prefix('queue')->group(function () {
        Route::get('get/{id}', 'queueGetCape');
        Route::get('list', 'queueList');
        Route::get('approve/{token}/{sha}/{type}', 'approveCape')->middleware('cape.token')->name('cape.approve');
        Route::post('upload/{token}', 'uploadCape')->middleware('cape.token');
    });
});

