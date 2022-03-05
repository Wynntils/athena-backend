<?php

use App\Http\Controllers\AuthController;

Route::controller(AuthController::class)->prefix('auth')->group(static function () {
    Route::get('getPublicKey', 'getPublicKey');

    Route::post('responseEncryption', 'responseEncryption');
});
