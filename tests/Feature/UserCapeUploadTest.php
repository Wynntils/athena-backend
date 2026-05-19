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

        $tmp = tempnam(sys_get_temp_dir(), 'cape') . '.png';
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
