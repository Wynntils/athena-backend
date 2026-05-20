<?php

use App\Enums\CosmeticStatus;
use App\Http\Libraries\CapeManager;
use App\Models\CosmeticAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Force GD driver so tests run without the Imagick extension
    config(['image.driver' => 'gd']);
    app()->singleton('image', fn() => new \Intervention\Image\ImageManager(['driver' => 'gd']));

    Storage::fake('queue');
    Storage::fake('approved');
    Storage::fake('banned');
    Storage::fake('special');

    // Bind a partial mock so getSha() returns a deterministic value without Imagick.
    // We must let the real constructor run so Storage disks are initialised.
    app()->bind(CapeManager::class, function () {
        $mock = Mockery::mock(CapeManager::class)->makePartial()->shouldAllowMockingProtectedMethods();
        // Call the real __construct to initialise the Storage disk references
        $mock->__construct();
        $mock->shouldReceive('getSha')->andReturnUsing(function ($image) {
            return sha1($image->width() . 'x' . $image->height() . 'salt');
        });
        return $mock;
    });
    $this->manager = app(CapeManager::class);

    $this->makeImage = function (int $w, int $h) {
        $img = imagecreatetruecolor($w, $h);
        ob_start(); imagepng($img); $data = ob_get_clean(); imagedestroy($img);
        $tmp = tempnam(sys_get_temp_dir(), 'cape') . '.png';
        file_put_contents($tmp, $data);
        $manager = new \Intervention\Image\ImageManager(['driver' => 'gd']);
        return $manager->make($tmp);
    };
});

it('isQueued returns true when cosmetic_assets row has status queued', function () {
    $image = ($this->makeImage)(64, 32);
    $sha   = $this->manager->queueCape($image, 'TestUser', false);

    expect($this->manager->isQueued($sha))->toBeTrue()
        ->and($this->manager->isApproved($sha))->toBeFalse();
});

it('isApproved returns true after approveCape', function () {
    $image = ($this->makeImage)(64, 32);
    $sha   = $this->manager->queueCape($image, 'TestUser', false);
    $this->manager->approveCape($sha);

    expect($this->manager->isApproved($sha))->toBeTrue()
        ->and($this->manager->isQueued($sha))->toBeFalse();
});

it('approveCape writes width and height to cosmetic_assets', function () {
    $image = ($this->makeImage)(64, 32);
    $sha   = $this->manager->queueCape($image, 'TestUser', false);
    $this->manager->approveCape($sha);

    $asset = CosmeticAsset::bySha($sha)->first();
    expect($asset->width)->toBe(64)->and($asset->height)->toBe(32);
});

it('approveCape writes system tags to cosmetic_assets', function () {
    $image = ($this->makeImage)(64, 32);
    $sha   = $this->manager->queueCape($image, 'TestUser', false);
    $this->manager->approveCape($sha);

    $asset = CosmeticAsset::bySha($sha)->first();
    expect($asset->tags)->toContain('size:64x32')
        ->and($asset->tags)->not->toContain('animated');
});

it('approveCape adds animated system tag for tall images', function () {
    $image = ($this->makeImage)(64, 64);
    $sha   = $this->manager->queueCape($image, 'TestUser', false);
    $this->manager->approveCape($sha);

    $asset = CosmeticAsset::bySha($sha)->first();
    expect($asset->tags)->toContain('animated');
});

it('isBanned returns true after banCape', function () {
    $image = ($this->makeImage)(64, 32);
    $sha   = $this->manager->queueCape($image, 'TestUser', false);
    $this->manager->banCape($sha);

    expect($this->manager->isBanned($sha))->toBeTrue()
        ->and($this->manager->isQueued($sha))->toBeFalse();
});

it('listCapes returns data from cosmetic_assets', function () {
    $image = ($this->makeImage)(64, 32);
    $sha   = $this->manager->queueCape($image, 'TestUser', false);
    $this->manager->approveCape($sha);

    $list = $this->manager->listCapes();
    expect($list)->toHaveCount(1)
        ->and($list[0]['sha'])->toBe($sha)
        ->and($list[0]['width'])->toBe(64)
        ->and($list[0]['height'])->toBe(32)
        ->and($list[0]['animated'])->toBeFalse();
});

it('queueCape stores uploader_id when User model is provided', function () {
    $user  = User::factory()->create();
    $image = ($this->makeImage)(64, 32);
    $sha   = $this->manager->queueCape($image, $user->username, false, $user);

    $asset = CosmeticAsset::bySha($sha)->first();
    expect($asset->uploader_id)->toBe($user->id);
});
