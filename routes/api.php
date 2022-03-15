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
    Route::middleware('auth.key')->group(static function() {
        Route::post('getUser/{apiKey}', 'getUser');
//        Route::post('setAccountType/{apiKey}', 'setAccountType');
//        Route::post('updateCosmetics/{apiKey}', 'updateCosmetics');
//        Route::post('setGuildColor/{apiKey}', 'setGuildColor');
//        Route::post('setUserPassword/{apiKey}', 'setUserPassword');
//        Route::post('getUserByPassword/{apiKey}', 'getUserByPassword');
        Route::post('createApiKey/{apiKey}', 'createApiKey');
        Route::post('changeApiKey/{apiKey}', 'changeApiKey');
        Route::post('getUserConfig/{apiKey}', 'getUserConfig');
    });
    Route::get('timings', 'timings');
});
