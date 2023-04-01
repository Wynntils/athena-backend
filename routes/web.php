<?php

use App\Http\Controllers\CrashReportController;
use App\Http\Controllers\WynntilsOAuthController;
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

if (config('app.debug') !== false) {
    Route::get('/phpinfo', static function () {
        return phpinfo();
    });
}

Route::get('oauth/{provider}', [WynntilsOAuthController::class, 'redirectToProvider'])->name('oauth.redirect');
Route::get('oauth/{provider}/callback', [WynntilsOAuthController::class, 'handleProviderCallback'])->name('oauth.callback');

Route::prefix('crash')->middleware(['auth', 'staff'])->group(static function () {
    Route::get('view/{crashReport}', [CrashReportController::class, 'view'])->name('crash.view');
    Route::get('/', [CrashReportController::class, 'index'])->name('crash.index');
    Route::put('/{crashReport}/handled', [CrashReportController::class, 'setHandled'])->name('crash.handled');
    Route::put('/{crashReport}/comment', [CrashReportController::class, 'addComment'])->name('crash.comment');
    Route::delete('/{crashReport}/comment', [CrashReportController::class, 'deleteComment'])->name('crash.comment.delete');
});

Route::prefix('auth')->name('auth.')->group(static function () {
    Route::middleware('guest')->group(function () {
        Route::view('login', 'auth.login')->name('login');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', static function () {
            auth()->logout();

            return redirect()->route('auth.login');
        })->name('logout');
    });
});

Route::fallback(static function () {
    return redirect('https://wynntils.com');
});
