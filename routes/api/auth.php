<?php

use App\Http\Controllers\AuthController;

Route::controller(AuthController::class)
    ->name('api.')
    ->group(static function () {
    Route::get('getPublicKey', 'getPublicKey')->name('getPublicKey');
    Route::post('responseEncryption', 'responseEncryption')->name('responseEncryption');
});
