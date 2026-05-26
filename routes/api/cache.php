<?php

use App\Http\Controllers\Cache\GuildListController;
use App\Http\Controllers\Cache\HashesController;
use App\Http\Controllers\Cache\ItemWeightsController;
use App\Http\Controllers\Cache\LeaderboardController;
use App\Http\Controllers\Cache\LootPoolsController;
use App\Http\Controllers\Cache\ServerListController;
use App\Http\Controllers\Cache\TerritoryListController;
use App\Http\Controllers\Cache\WorldEventsController;

Route::get('get/guildList', GuildListController::class)->name('cache.guildList');
Route::redirect('get/guildListWithColors', '/cache/get/guildList', 301);
Route::get('get/serverList', ServerListController::class)->name('cache.serverList');
Route::get('get/itemWeights', ItemWeightsController::class)->name('cache.itemWeights');
Route::get('get/leaderboard', LeaderboardController::class)->name('cache.leaderboard');
Route::get('get/territoryList', TerritoryListController::class)->name('cache.territoryList');
Route::get('get/worldEvents', WorldEventsController::class)->name('cache.worldEvents');
Route::get('get/lootPools', LootPoolsController::class)->name('cache.lootPools');
Route::get('getHashes', HashesController::class)->name('cache.getHashes');
