<?php

use App\Enums\AccountType;
use App\Enums\CosmeticSlot;
use App\Enums\CosmeticStatus;
use App\Enums\CosmeticType;
use App\Enums\CosmeticVisibility;
use App\Models\CosmeticAsset;
use App\Models\CosmeticVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

// ---------------------------------------------------------------------------
// GET /cosmetics — index
// ---------------------------------------------------------------------------

it('returns paginated approved public assets', function () {
    CosmeticAsset::factory()->approved()->count(3)->create(['visibility' => CosmeticVisibility::PUBLIC]);
    // QUEUED asset should not appear
    CosmeticAsset::factory()->create(['status' => CosmeticStatus::QUEUED, 'visibility' => CosmeticVisibility::PUBLIC]);
    // PRIVATE asset should not appear
    CosmeticAsset::factory()->approved()->create(['visibility' => CosmeticVisibility::PRIVATE]);

    $response = $this->getJson('/cosmetics');

    $response->assertOk();
    $data = $response->json('data');
    expect($data)->toHaveCount(3);
    // Pagination keys present
    $response->assertJsonStructure(['data', 'current_page', 'per_page', 'total', 'last_page']);
});

it('filters by search query', function () {
    CosmeticAsset::factory()->approved()->create(['name' => 'Dragon Wings', 'visibility' => CosmeticVisibility::PUBLIC]);
    CosmeticAsset::factory()->approved()->create(['name' => 'Angel Wings', 'visibility' => CosmeticVisibility::PUBLIC]);
    CosmeticAsset::factory()->approved()->create(['name' => 'Cape', 'visibility' => CosmeticVisibility::PUBLIC]);

    $response = $this->getJson('/cosmetics?q=Wings');

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(2);
});

it('filters by tags', function () {
    CosmeticAsset::factory()->approved()->create(['tags' => ['pvp', 'wings'], 'visibility' => CosmeticVisibility::PUBLIC]);
    CosmeticAsset::factory()->approved()->create(['tags' => ['wings'], 'visibility' => CosmeticVisibility::PUBLIC]);
    CosmeticAsset::factory()->approved()->create(['tags' => ['pvp'], 'visibility' => CosmeticVisibility::PUBLIC]);
    CosmeticAsset::factory()->approved()->create(['tags' => [], 'visibility' => CosmeticVisibility::PUBLIC]);

    // Asset must contain ALL specified tags
    $response = $this->getJson('/cosmetics?' . http_build_query(['tags' => ['pvp', 'wings']]));

    $response->assertOk();
    expect($response->json('data'))->toHaveCount(1);
});

it('sorts by newest by default', function () {
    $older = CosmeticAsset::factory()->approved()->create([
        'visibility'  => CosmeticVisibility::PUBLIC,
        'uploaded_at' => now()->subDays(2),
    ]);
    $newer = CosmeticAsset::factory()->approved()->create([
        'visibility'  => CosmeticVisibility::PUBLIC,
        'uploaded_at' => now(),
    ]);

    $data = $this->getJson('/cosmetics?sort=newest')->json('data');
    expect($data[0]['sha'])->toBe($newer->sha);
    expect($data[1]['sha'])->toBe($older->sha);
});

it('sorts by worn (equip_count)', function () {
    $low  = CosmeticAsset::factory()->approved()->create(['visibility' => CosmeticVisibility::PUBLIC, 'equip_count' => 1]);
    $high = CosmeticAsset::factory()->approved()->create(['visibility' => CosmeticVisibility::PUBLIC, 'equip_count' => 99]);

    $data = $this->getJson('/cosmetics?sort=worn')->json('data');
    expect($data[0]['sha'])->toBe($high->sha);
});

it('sorts by votes', function () {
    $uploader = User::factory()->create();
    $voter    = User::factory()->create();

    $popular = CosmeticAsset::factory()->approved()->create(['visibility' => CosmeticVisibility::PUBLIC, 'uploader_id' => $uploader->id]);
    $plain   = CosmeticAsset::factory()->approved()->create(['visibility' => CosmeticVisibility::PUBLIC, 'uploader_id' => $uploader->id]);

    CosmeticVote::create(['cosmetic_id' => $popular->id, 'user_id' => $voter->id, 'vote' => 1]);

    $data = $this->getJson('/cosmetics?sort=votes')->json('data');
    expect($data[0]['sha'])->toBe($popular->sha);
});

it('paginates 15 per page', function () {
    CosmeticAsset::factory()->approved()->count(20)->create(['visibility' => CosmeticVisibility::PUBLIC]);

    $response = $this->getJson('/cosmetics');
    $response->assertOk();
    expect($response->json('data'))->toHaveCount(15);
    expect($response->json('per_page'))->toBe(15);
});

// ---------------------------------------------------------------------------
// GET /cosmetics/{sha} — show
// ---------------------------------------------------------------------------

it('returns a single approved public asset', function () {
    $asset = CosmeticAsset::factory()->approved()->create([
        'visibility' => CosmeticVisibility::PUBLIC,
        'name'       => 'Test Asset',
    ]);

    $this->getJson("/cosmetics/{$asset->sha}")
        ->assertOk()
        ->assertJsonPath('sha', $asset->sha)
        ->assertJsonPath('name', 'Test Asset');
});

it('returns 404 for unknown sha', function () {
    $this->getJson('/cosmetics/' . str_repeat('0', 40))->assertNotFound();
});

it('returns 404 for non-approved asset', function () {
    $asset = CosmeticAsset::factory()->create([
        'status'     => CosmeticStatus::QUEUED,
        'visibility' => CosmeticVisibility::PUBLIC,
    ]);

    $this->getJson("/cosmetics/{$asset->sha}")->assertNotFound();
});

it('returns 404 for private asset when unauthenticated', function () {
    $asset = CosmeticAsset::factory()->approved()->create(['visibility' => CosmeticVisibility::PRIVATE]);

    $this->getJson("/cosmetics/{$asset->sha}")->assertNotFound();
});

// ---------------------------------------------------------------------------
// POST /cosmetics/{sha}/vote
// ---------------------------------------------------------------------------

it('requires auth to vote', function () {
    $asset = CosmeticAsset::factory()->approved()->create(['visibility' => CosmeticVisibility::PUBLIC]);

    $this->postJson("/cosmetics/{$asset->sha}/vote", ['vote' => 1])->assertUnauthorized();
});

it('casts an upvote', function () {
    $uploader = User::factory()->create();
    $voter    = User::factory()->create();
    $asset    = CosmeticAsset::factory()->approved()->create([
        'visibility'  => CosmeticVisibility::PUBLIC,
        'uploader_id' => $uploader->id,
    ]);

    $this->withHeaders(['authToken' => $voter->auth_token])
        ->postJson("/cosmetics/{$asset->sha}/vote", ['vote' => 1])
        ->assertOk();

    expect(CosmeticVote::where('cosmetic_id', $asset->id)->where('user_id', $voter->id)->value('vote'))->toBe(1);
});

it('returns 403 for self-vote', function () {
    $user  = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create([
        'visibility'  => CosmeticVisibility::PUBLIC,
        'uploader_id' => $user->id,
    ]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson("/cosmetics/{$asset->sha}/vote", ['vote' => 1])
        ->assertForbidden();
});

it('returns 403 for banned user voting', function () {
    $uploader = User::factory()->create();
    $banned   = User::factory()->create(['account_type' => AccountType::BANNED]);
    $asset    = CosmeticAsset::factory()->approved()->create([
        'visibility'  => CosmeticVisibility::PUBLIC,
        'uploader_id' => $uploader->id,
    ]);

    $this->withHeaders(['authToken' => $banned->auth_token])
        ->postJson("/cosmetics/{$asset->sha}/vote", ['vote' => 1])
        ->assertForbidden();
});

it('rejects invalid vote value', function () {
    $user  = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create(['visibility' => CosmeticVisibility::PUBLIC]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->postJson("/cosmetics/{$asset->sha}/vote", ['vote' => 2])
        ->assertUnprocessable();
});

// ---------------------------------------------------------------------------
// DELETE /cosmetics/{sha}/vote
// ---------------------------------------------------------------------------

it('requires auth to unvote', function () {
    $asset = CosmeticAsset::factory()->approved()->create(['visibility' => CosmeticVisibility::PUBLIC]);

    $this->deleteJson("/cosmetics/{$asset->sha}/vote")->assertUnauthorized();
});

it('removes a vote', function () {
    $uploader = User::factory()->create();
    $voter    = User::factory()->create();
    $asset    = CosmeticAsset::factory()->approved()->create([
        'visibility'  => CosmeticVisibility::PUBLIC,
        'uploader_id' => $uploader->id,
    ]);

    CosmeticVote::create(['cosmetic_id' => $asset->id, 'user_id' => $voter->id, 'vote' => 1]);

    $this->withHeaders(['authToken' => $voter->auth_token])
        ->deleteJson("/cosmetics/{$asset->sha}/vote")
        ->assertOk();

    expect(CosmeticVote::where('cosmetic_id', $asset->id)->where('user_id', $voter->id)->exists())->toBeFalse();
});

it('returns 403 for banned user unvoting', function () {
    $uploader = User::factory()->create();
    $banned   = User::factory()->create(['account_type' => AccountType::BANNED]);
    $asset    = CosmeticAsset::factory()->approved()->create([
        'visibility'  => CosmeticVisibility::PUBLIC,
        'uploader_id' => $uploader->id,
    ]);

    CosmeticVote::create(['cosmetic_id' => $asset->id, 'user_id' => $banned->id, 'vote' => 1]);

    $this->withHeaders(['authToken' => $banned->auth_token])
        ->deleteJson("/cosmetics/{$asset->sha}/vote")
        ->assertForbidden();
});

// ---------------------------------------------------------------------------
// PATCH /cosmetics/{sha}
// ---------------------------------------------------------------------------

it('requires auth to patch', function () {
    $asset = CosmeticAsset::factory()->approved()->create(['visibility' => CosmeticVisibility::PUBLIC]);

    $this->patchJson("/cosmetics/{$asset->sha}", ['name' => 'New Name'])->assertUnauthorized();
});

it('allows uploader to submit an edit', function () {
    $user  = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create([
        'visibility'  => CosmeticVisibility::PUBLIC,
        'uploader_id' => $user->id,
    ]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->patchJson("/cosmetics/{$asset->sha}", ['name' => 'Fancy Cape'])
        ->assertOk();

    expect($asset->fresh()->pending_name)->toBe('Fancy Cape');
});

it('returns 403 for non-uploader trying to edit', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create([
        'visibility'  => CosmeticVisibility::PUBLIC,
        'uploader_id' => $owner->id,
    ]);

    $this->withHeaders(['authToken' => $other->auth_token])
        ->patchJson("/cosmetics/{$asset->sha}", ['name' => 'Hacked'])
        ->assertForbidden();
});

it('returns 409 when a pending edit already exists', function () {
    $user  = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create([
        'visibility'   => CosmeticVisibility::PUBLIC,
        'uploader_id'  => $user->id,
        'pending_name' => 'Already Pending',
    ]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->patchJson("/cosmetics/{$asset->sha}", ['name' => 'Another Edit'])
        ->assertStatus(409);
});

it('returns 403 for banned user patching', function () {
    $banned = User::factory()->create(['account_type' => AccountType::BANNED]);
    $asset  = CosmeticAsset::factory()->approved()->create([
        'visibility'  => CosmeticVisibility::PUBLIC,
        'uploader_id' => $banned->id,
    ]);

    $this->withHeaders(['authToken' => $banned->auth_token])
        ->patchJson("/cosmetics/{$asset->sha}", ['name' => 'Whatever'])
        ->assertForbidden();
});

it('validates patch fields', function () {
    $user  = User::factory()->create();
    $asset = CosmeticAsset::factory()->approved()->create([
        'visibility'  => CosmeticVisibility::PUBLIC,
        'uploader_id' => $user->id,
    ]);

    $this->withHeaders(['authToken' => $user->auth_token])
        ->patchJson("/cosmetics/{$asset->sha}", ['visibility' => 'invalid_value'])
        ->assertUnprocessable();
});
