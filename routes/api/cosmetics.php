<?php

use App\Http\Controllers\CosmeticsController;

Route::controller(CosmeticsController::class)->group(static function () {
    Route::get('/', 'index')->name('index');
    Route::get('{sha}', 'show')->name('show');

    Route::middleware('auth:token,sanctum')->group(function () {
        Route::post('{sha}/vote', 'vote')->name('vote');
        Route::delete('{sha}/vote', 'unvote')->name('unvote');
        Route::patch('{sha}', 'update')->name('update');
    });
});
