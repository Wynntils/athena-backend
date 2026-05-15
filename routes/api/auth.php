<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WebAuthController;

Route::controller(AuthController::class)
    ->name('api.')
    ->group(static function () {
        Route::get('getPublicKey', 'getPublicKey')->name('getPublicKey');
        Route::post('responseEncryption', 'responseEncryption')->name('responseEncryption');
    });

Route::post('login', [WebAuthController::class, 'login'])->name('login');
