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

Route::controller(LegacyApiController::class)->middleware('athena.token')->group(static function () {
    Route::post('getUser/{apiKey}', 'getUserData');
    Route::post('getUserConfig/{apiKey}', 'getUserConfig');
    Route::post('setAccountType/{apiKey}', 'setAccountType');
    Route::post('updateCosmetics/{apiKey}', 'updateCosmetics');
    Route::post('setGuildColor/{apiKey}', 'setGuildColor');
    Route::post('setUserPassword/{apiKey}', 'setUserPassword');
    Route::post('getUserByPassword/{apiKey}', 'getUserByPassword');
});


Route::get('/health', function () {
    return ['result' => 'ok'];
});
