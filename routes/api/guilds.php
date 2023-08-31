<?php

use \App\Http\Controllers\GuildController;

Route::controller(GuildController::class)->group(static function() {
   Route::get('list/{filter?}', 'list')->name('list');
   Route::post('setColor/{apiKey}', 'setColor')->middleware(['athena.token'])->name('setColor');
});
