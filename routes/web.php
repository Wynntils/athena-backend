<?php

use App\Http\Controllers\VersionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', static function () {
    return ['Laravel' => app()->version()];
});

if(config('app.debug') !== false) {
    Route::get('/phpinfo', static function () {
        return phpinfo();
    });
}

Route::prefix('version')->group(static function () {
    Route::get('latest/{stream}', [VersionController::class, 'latest'])->where('stream', 're|ce')->name('version.latest');
    Route::get('changelog/{version}', [VersionController::class, 'changelog'])->name('version.changelog');
});

Route::fallback(static function () {
    return redirect('https://wynntils.com');
});
