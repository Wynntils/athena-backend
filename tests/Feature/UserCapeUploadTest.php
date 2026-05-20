<?php

use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('queue');
    Storage::fake('approved');
    Storage::fake('banned');
    Storage::fake('special');

    $this->makePng = function (int $w, int $h): UploadedFile {
        $img = imagecreatetruecolor($w, $h);
        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        $tmp = tempnam(sys_get_temp_dir(), 'cape').'.png';
        file_put_contents($tmp, $data);

        return new UploadedFile($tmp, 'cape.png', 'image/png', null, true);
    };
});

it('queues a valid 64x32 cape for a NORMAL user', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 32)])
        ->assertOk()
        ->assertJsonStructure(['message', 'sha-1', 'animated'])
        ->assertJsonPath('animated', false);
});

it('rejects oversized upload for NORMAL user', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(128, 64)])
        ->assertStatus(400)
        ->assertJsonPath('message', 'Resolution exceeds your account tier.');
});

it('rejects animated upload for NORMAL user', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 64)])
        ->assertStatus(400)
        ->assertJsonPath('message', 'Animated capes require a Donator account or higher.');
});

it('accepts 256x128 for DONATOR user', function () {
    $user = User::factory()->create(['account_type' => AccountType::DONATOR]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(256, 128)])
        ->assertOk();
});

it('rejects 512x256 for DONATOR user', function () {
    $user = User::factory()->create(['account_type' => AccountType::DONATOR]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(512, 256)])
        ->assertStatus(400)
        ->assertJsonPath('message', 'Resolution exceeds your account tier.');
});

it('accepts animated cape for DONATOR user', function () {
    $user = User::factory()->create(['account_type' => AccountType::DONATOR]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 64)])
        ->assertOk()
        ->assertJsonPath('animated', true);
});

it('accepts any resolution for MODERATOR user', function () {
    $user = User::factory()->create(['account_type' => AccountType::MODERATOR]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(512, 256)])
        ->assertOk();
});

it('rejects BANNED user', function () {
    $user = User::factory()->create(['account_type' => AccountType::BANNED]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 32)])
        ->assertStatus(403);
});

it('rejects duplicate cape', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 32)])
        ->assertOk();

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 32)])
        ->assertStatus(400)
        ->assertJsonPath('message', 'The provided cape is already queued.');
});

it('invalidates capes.list cache on success', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);
    Cache::spy();

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 32)])
        ->assertOk();

    Cache::shouldHaveReceived('forget')->with('capes.list')->once();
});

it('returns 401 without authToken', function () {
    $this->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 32)])
        ->assertStatus(401);
});

it('creates a cosmetic_assets row with uploader_id on new upload', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 32)])
        ->assertOk();

    expect(\App\Models\CosmeticAsset::where('uploader_id', $user->id)->exists())->toBeTrue();
});

it('returns 200 without creating duplicate row when SHA already approved', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);

    // First upload
    $response1 = $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 32), 'name' => 'Original'])
        ->assertOk();

    $sha = $response1->json('sha-1');

    // Manually approve the cape in DB and move file
    \App\Models\CosmeticAsset::bySha($sha)->update(['status' => \App\Enums\CosmeticStatus::APPROVED]);
    Storage::disk('approved')->put($sha, Storage::disk('queue')->get($sha));
    Storage::disk('queue')->delete($sha);

    // Second upload of same image by different user
    $user2 = User::factory()->create(['account_type' => AccountType::NORMAL]);
    $this->withHeaders(['authToken' => $user2->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 32), 'name' => 'Override Attempt'])
        ->assertOk()
        ->assertJsonPath('message', 'The provided cape is already approved.');

    // Name should still be null (original upload didn't set it either) and count stays at 1
    expect(\App\Models\CosmeticAsset::where('sha', $sha)->count())->toBe(1);
});

it('stores name and visibility on upload', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);

    $response = $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', [
            'cape' => ($this->makePng)(64, 32),
            'name' => 'My Cool Cape',
            'visibility' => 'private',
        ])
        ->assertOk();

    $sha = $response->json('sha-1');
    $asset = \App\Models\CosmeticAsset::bySha($sha)->first();

    expect($asset->name)->toBe('My Cool Cape')
        ->and($asset->visibility->value)->toBe('private');
});

it('sets active cape and updates equip_count when uploading an already-approved cape', function () {
    $user = User::factory()->create(['account_type' => AccountType::NORMAL]);

    // First upload — queues the cape and gives us the real SHA
    $sha = $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 32)])
        ->assertOk()->json('sha-1');

    // Approve it manually
    \App\Models\CosmeticAsset::bySha($sha)->update(['status' => \App\Enums\CosmeticStatus::APPROVED, 'equip_count' => 0]);
    Storage::disk('approved')->put($sha, Storage::disk('queue')->get($sha));
    Storage::disk('queue')->delete($sha);

    // Same user uploads the same image again — should hit the already-approved branch
    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson('/user/cape/upload', ['cape' => ($this->makePng)(64, 32)])
        ->assertOk()
        ->assertJsonPath('message', 'The provided cape is already approved.');

    expect($user->fresh()->cosmetic_info['capeTexture'])->toBe($sha)
        ->and(\App\Models\CosmeticAsset::bySha($sha)->value('equip_count'))->toBe(1);
});
