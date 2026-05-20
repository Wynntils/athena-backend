<?php

use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('approved');
    Storage::fake('special');

    $this->makeApprovedCape = function (string $sha, bool $animated = false): void {
        $h = $animated ? 64 : 32;
        $img = imagecreatetruecolor(64, $h);
        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);
        Storage::disk('approved')->put($sha, $data);

        \App\Models\CosmeticAsset::firstOrCreate(['sha' => $sha], [
            'type' => \App\Enums\CosmeticType::TEXTURE,
            'slot' => \App\Enums\CosmeticSlot::BACK,
            'status' => \App\Enums\CosmeticStatus::APPROVED,
            'width' => 64,
            'height' => $h,
            'visibility' => \App\Enums\CosmeticVisibility::PUBLIC,
            'tags' => [],
            'uploaded_at' => now(),
        ]);

        $existing = Cache::get('capes.list', []);
        $existing[] = ['sha' => $sha, 'width' => 64, 'height' => $h, 'animated' => $animated];
        Cache::put('capes.list', $existing, 86400);
    };
});

it('sets capeTexture for an approved SHA', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);
    $sha = str_repeat('a', 40);
    ($this->makeApprovedCape)($sha);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/select', ['sha' => $sha])
        ->assertOk()
        ->assertJsonPath('message', 'Cape updated.');

    expect($user->fresh()->cosmetic_info['capeTexture'])->toBe($sha);
});

it('clears capeTexture when sha is empty', function () {
    $user = User::factory()->create([
        'account_type' => AccountType::NORMAL,
        'cosmetic_info' => ['capeTexture' => str_repeat('a', 40)],
    ]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/select', ['sha' => ''])
        ->assertOk()
        ->assertJsonPath('message', 'Cape cleared.');

    expect($user->fresh()->cosmetic_info['capeTexture'])->toBe('');
});

it('returns 404 for unknown SHA', function () {
    $user = User::factory()->create();

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/select', ['sha' => str_repeat('b', 40)])
        ->assertNotFound();
});

it('rejects animated cape for NORMAL user', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);
    $sha = str_repeat('c', 40);
    ($this->makeApprovedCape)($sha, animated: true);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/select', ['sha' => $sha])
        ->assertStatus(403)
        ->assertJsonPath('message', 'Animated capes require a Donator account or higher.');
});

it('allows animated cape for DONATOR user', function () {
    $user = User::factory()->create(['account_type' => AccountType::DONATOR]);
    $sha = str_repeat('d', 40);
    ($this->makeApprovedCape)($sha, animated: true);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/select', ['sha' => $sha])
        ->assertOk();
});

it('invalidates user cache after update', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);
    $sha = str_repeat('e', 40);
    ($this->makeApprovedCape)($sha);

    Cache::spy();

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/select', ['sha' => $sha]);

    Cache::shouldHaveReceived('forget')->with("user-{$user->id}")->once();
});

it('returns 403 for BANNED user', function () {
    $user = User::factory()->create(['account_type' => AccountType::BANNED]);
    $sha = str_repeat('a', 40);
    ($this->makeApprovedCape)($sha);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/select', ['sha' => $sha])
        ->assertStatus(403);
});

it('returns 401 without authToken', function () {
    $this->postJson('/user/cape/select', ['sha' => str_repeat('a', 40)])
        ->assertStatus(401);
});

it('increments equip_count on the new cape', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);
    $sha = str_repeat('a', 40);
    ($this->makeApprovedCape)($sha);
    \App\Models\CosmeticAsset::bySha($sha)->update(['equip_count' => 0]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/select', ['sha' => $sha])
        ->assertOk();

    expect(\App\Models\CosmeticAsset::bySha($sha)->value('equip_count'))->toBe(1);
});

it('decrements old cape equip_count when switching', function () {
    $oldSha = str_repeat('a', 40);
    $newSha = str_repeat('b', 40);
    $user = User::factory()->create([
        'account_type' => AccountType::NORMAL,
        'cosmetic_info' => ['capeTexture' => $oldSha],
    ]);
    ($this->makeApprovedCape)($oldSha);
    ($this->makeApprovedCape)($newSha);
    \App\Models\CosmeticAsset::bySha($oldSha)->update(['equip_count' => 3]);
    \App\Models\CosmeticAsset::bySha($newSha)->update(['equip_count' => 0]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/select', ['sha' => $newSha])
        ->assertOk();

    expect(\App\Models\CosmeticAsset::bySha($oldSha)->value('equip_count'))->toBe(2)
        ->and(\App\Models\CosmeticAsset::bySha($newSha)->value('equip_count'))->toBe(1);
});

it('decrements equip_count when clearing cape', function () {
    $sha = str_repeat('a', 40);
    $user = User::factory()->create([
        'account_type' => AccountType::NORMAL,
        'cosmetic_info' => ['capeTexture' => $sha],
    ]);
    ($this->makeApprovedCape)($sha);
    \App\Models\CosmeticAsset::bySha($sha)->update(['equip_count' => 5]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/select', ['sha' => ''])
        ->assertOk();

    expect(\App\Models\CosmeticAsset::bySha($sha)->value('equip_count'))->toBe(4);
});
