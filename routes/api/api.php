<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

use App\Http\Controllers\LegacyApiController;

Route::controller(LegacyApiController::class)
    ->name('legacy.')
    ->middleware('athena.token')->group(static function () {
        Route::post('getUser/{apiKey}', 'getUserData')->name('getUser');
        Route::post('getLinkedUsers/{apiKey}', 'getLinkedUsersData')->name('getLinkedUsers');
        Route::post('getUserConfig/{apiKey}', 'getUserConfig')->name('getUserConfig');
        Route::post('setAccountType/{apiKey}', 'setAccountType')->name('setAccountType');
        Route::post('updateCosmetics/{apiKey}', 'updateCosmetics')->name('updateCosmetics');
        Route::post('setGuildColor/{apiKey}', 'setGuildColor')->name('setGuildColor');
        Route::post('setUserPassword/{apiKey}', 'setUserPassword')->name('setUserPassword');
        Route::post('getUserByPassword/{apiKey}', 'getUserByPassword')->name('getUserByPassword');
    });

Route::get('/health', function () {
    return ['result' => 'ok'];
})->name('health');

Route::get('/docs', function () {
    return view('docs');
})->name('docs');
