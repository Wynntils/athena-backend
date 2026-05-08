<?php

use App\Models\CrashReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('unhandled scope returns only unhandled crash reports', function () {
    CrashReport::factory()->create(['handled' => false]);
    CrashReport::factory()->create(['handled' => true]);

    $result = CrashReport::unhandled()->get();

    expect($result)->toHaveCount(1)
        ->and($result->first()->handled)->toBeFalse();
});

it('unhandled scope returns empty when all crash reports are handled', function () {
    CrashReport::factory()->create(['handled' => true]);
    CrashReport::factory()->create(['handled' => true]);

    $result = CrashReport::unhandled()->get();

    expect($result)->toHaveCount(0);
});
