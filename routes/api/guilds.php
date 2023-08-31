<?php

use \App\Http\Controllers\GuildController;

Route::controller(GuildController::class)->group(static function() {
   Route::post('setColor/{apiKey}', 'setColor')->middleware(['athena.token'])->name('setColor');
});
