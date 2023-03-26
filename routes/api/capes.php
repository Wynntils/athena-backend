<?php

use App\Http\Controllers\CapeController;

Route::controller(CapeController::class)->group(static function () {
    Route::get('get/{id}', 'getCape')->name('getCape');
    Route::get('user/{uuid}', 'getUserCape')->name('getUserCape');
    Route::get('list', 'list')->name('cape.list');
    Route::get('ban/{token}/{sha}', 'banCape')->middleware('cape.token')->name('ban');
    Route::prefix('queue')->name('queue.')->group(function () {
        Route::get('get/{sha}', 'queueGetCape')->name('get');
        Route::get('list', 'queueList')->name('list');
        Route::get('approve/{token}/{sha}/{type}', 'approveCape')->middleware('cape.token')->name('approve');
        Route::post('upload/{token}', 'uploadCape')->middleware('token');
    });
});
