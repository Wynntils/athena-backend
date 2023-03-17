<?php

use App\Http\Controllers\WebhookController;

Route::post('github', [WebhookController::class, 'github'])->name('github');
