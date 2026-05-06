<?php

use App\Http\Controllers\Cache\GuildListController;
use App\Http\Controllers\Cache\HashesController;
use App\Http\Controllers\Cache\ItemWeightsController;
use App\Http\Controllers\Cache\LeaderboardController;
use App\Http\Controllers\Cache\ServerListController;
use App\Http\Controllers\Cache\TerritoryListController;

Route::get('get/guildList', GuildListController::class)->name('cache.guildList');
Route::get('get/serverList', ServerListController::class)->name('cache.serverList');
Route::get('get/itemWeights', ItemWeightsController::class)->name('cache.itemWeights');
Route::get('get/leaderboard', LeaderboardController::class)->name('cache.leaderboard');
Route::get('get/territoryList', TerritoryListController::class)->name('cache.territoryList');
Route::get('getHashes', HashesController::class)->name('cache.getHashes');
