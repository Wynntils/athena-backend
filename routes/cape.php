<?php

use App\Http\Controllers\CapeController;
use App\Http\Controllers\V2\CapeController as CapeControllerV2;

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

Route::controller(CapeControllerV2::class)->prefix('/v2')->group(static function () {
    Route::get('/info/{id}', 'getCapeInfo');
    Route::prefix('queue')->group(function () {
        Route::post('upload/{token}', 'uploadCape')->middleware(['cape.token', 'auth:token']);
    });
});
