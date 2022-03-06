<?php

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

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

Route::controller(ApiController::class)->group(static function () {
    Route::post('getUser/{apiKey}', 'getUser')->middleware('auth.key');
    Route::post('setAccountType/{apiKey}', 'setAccountType')->middleware('auth.key');
    Route::post('updateCosmetics/{apiKey}', 'updateCosmetics')->middleware('auth.key');
    Route::post('setGuildColor/{apiKey}', 'setGuildColor')->middleware('auth.key');
    Route::post('setUserPassword/{apiKey}', 'setUserPassword')->middleware('auth.key');
    Route::post('getUserByPassword/{apiKey}', 'getUserByPassword')->middleware('auth.key');
    Route::post('createApiKey/{apiKey}', 'createApiKey')->middleware('auth.key');
    Route::post('changeApiKey/{apiKey}', 'changeApiKey')->middleware('auth.key');
    Route::post('getUserConfig/{apiKey}', 'getUserConfig')->middleware('auth.key');
    Route::get('timings', 'timings');
});
