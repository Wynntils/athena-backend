<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\DonatorType;
use ArrayObject;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $id
 * @property string $username
 * @property string $authToken
 * @property AccountType $accountType
 * @property DonatorType $donatorType
 * @property array|null $discordInfo
 * @property array|null $cosmeticInfo
 * @property array|null $usedVersions
 * @property string|null $latestVersion
 * @property int|null $lastActivity
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'accountType',
        'donatorType',
        'username',
        'password',
        'auth_token',
        'last_activity',
        'latest_version',
        'discord_info',
        'cosmetic_info',
        'used_versions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'auth_token',
        'remember_token',
    ];

    protected $casts = [
        'accountType' => AccountType::class,
        'donatorType' => DonatorType::class,
        'discord_info' => 'array',
        'cosmetic_info' => 'array',
        'used_versions' => 'array',
        'last_activity' => 'integer',
    ];

    // Accessor for MongoDB compatibility
    public function getAuthTokenAttribute($value)
    {
        return $this->attributes['auth_token'] ?? $value;
    }

    // Mutator for MongoDB compatibility
    public function setAuthTokenAttribute($value)
    {
        $this->attributes['auth_token'] = $value;
    }

    // Accessor for discordInfo
    public function getDiscordInfoAttribute($value)
    {
        $result = $this->attributes['discord_info'] ?? $value;

        // If it's a string (JSON), decode it to an array
        if (is_string($result)) {
            $decoded = json_decode($result, true);

            return is_array($decoded) ? $decoded : null;
        }

        // If it's already an array, return it
        if (is_array($result)) {
            return $result;
        }

        return $result;
    }

    // Mutator for discordInfo
    public function setDiscordInfoAttribute($value)
    {
        $this->attributes['discord_info'] = $value;
    }

    // Accessor for cosmeticInfo
    public function getCosmeticInfoAttribute($value)
    {
        $result = $this->attributes['cosmetic_info'] ?? $value;

        // If it's a string (JSON), decode it to an array
        if (is_string($result)) {
            $decoded = json_decode($result, true);

            return is_array($decoded) ? $decoded : null;
        }

        // If it's already an array, return it
        if (is_array($result)) {
            return $result;
        }

        return $result;
    }

    // Mutator for cosmeticInfo
    public function setCosmeticInfoAttribute($value)
    {
        $this->attributes['cosmetic_info'] = $value;
    }

    // Accessor for usedVersions
    public function getUsedVersionsAttribute($value)
    {
        $result = $this->attributes['used_versions'] ?? $value;

        // If it's a string (JSON), decode it to an array
        if (is_string($result)) {
            $decoded = json_decode($result, true);

            return is_array($decoded) ? $decoded : [];
        }

        // If it's already an array, return it
        if (is_array($result)) {
            return $result;
        }

        // Default to empty array
        return [];
    }

    // Mutator for usedVersions
    public function setUsedVersionsAttribute($value)
    {
        $this->attributes['used_versions'] = $value;
    }

    // Accessor for lastActivity
    public function getLastActivityAttribute($value)
    {
        return $this->attributes['last_activity'] ?? $value;
    }

    // Mutator for lastActivity
    public function setLastActivityAttribute($value)
    {
        $this->attributes['last_activity'] = $value;
    }

    // Accessor for latestVersion
    public function getLatestVersionAttribute($value)
    {
        return $this->attributes['latest_version'] ?? $value;
    }

    // Mutator for latestVersion
    public function setLatestVersionAttribute($value)
    {
        $this->attributes['latest_version'] = $value;
    }

    public function updateAccount($username, $version): void
    {
        $this->auth_token = \Str::uuid()->toString();
        $this->last_activity = currentTimeMillis();
        $this->username = $username;

        $this->latest_version = $version;

        // Ensure used_versions is always an array
        $usedVersions = $this->used_versions;
        if (! is_array($usedVersions)) {
            $usedVersions = [];
        }
        $usedVersions[$version] = currentTimeMillis();
        $this->used_versions = $usedVersions;

        $this->save();
    }

    public function updateDiscord($id, $username): void
    {
        $this->discord_info = [
            'id' => $id,
            'username' => $username,
        ];
        $this->save();
    }

    public function getConfigs(): ArrayObject
    {
        $files = new ArrayObject;
        $configs = Storage::disk('configs');
        foreach ($configs->files($this->id) as $file) {
            $files[basename($file)] = zlib_decode($configs->get($file));
        }

        return $files;
    }

    public function uploadConfig(UploadedFile $file): bool
    {
        if (in_array($this->id, config('athena.debug.users'))) {
            \Log::info('Uploading config for user '.$this->id, ['file' => $file->getClientOriginalName()]);
        }

        return Storage::disk('configs')->put($this->id.'/'.$file->getClientOriginalName(), zlib_encode(json_encode(json_decode(file_get_contents($file))), ZLIB_ENCODING_DEFLATE));
    }

    public function getConfigAmount(): int
    {
        return count(Storage::disk('configs')->files($this->id));
    }

    public function deleteConfig(string $file): bool
    {
        $configs = Storage::disk('configs');

        if (! $configs->exists($this->id.'/'.$file)) {
            return false;
        }

        $configs->delete($this->id.'/'.$file);

        return true;
    }

    public function getConfig(string $file): ?string
    {
        $configs = Storage::disk('configs');

        if (! $configs->exists($this->id.'/'.$file)) {
            return null;
        }

        return json_encode(json_decode(zlib_decode($configs->get($this->id.'/'.$file))));
    }

    public function getConfigFiles(): array
    {
        $configs = Storage::disk('configs');

        return collect($configs->files($this->id))->map(function ($file) {
            return basename($file);
        })->toArray();
    }

    // Cosmetic Info Helper Methods
    public function hasCape(): bool
    {
        if (\App\Http\Libraries\CapeManager::instance()->isSpecialDate()) {
            return true;
        }

        $cosmeticInfo = $this->cosmetic_info ?? [];
        $elytraEnabled = $cosmeticInfo['elytraEnabled'] ?? false;

        return ! $elytraEnabled && $this->isTextureValid();
    }

    private function isTextureValid(): bool
    {
        $cosmeticInfo = $this->cosmetic_info ?? [];
        $capeTexture = $cosmeticInfo['capeTexture'] ?? '';

        return ! empty($capeTexture) && Storage::disk('approved')->exists($capeTexture);
    }

    public function hasPart($part): bool
    {
        $cosmeticInfo = $this->cosmetic_info ?? [];
        $parts = $cosmeticInfo['parts'] ?? [];

        return ! empty($parts) && ($parts[$part] ?? false) === true;
    }

    public function hasElytra(): bool
    {
        if (\App\Http\Libraries\CapeManager::instance()->isSpecialDate()) {
            return false;
        }

        $cosmeticInfo = $this->cosmetic_info ?? [];
        $elytraEnabled = $cosmeticInfo['elytraEnabled'] ?? false;

        return $elytraEnabled && $this->isTextureValid();
    }

    public function getFormattedTexture(): string
    {
        return $this->isTextureValid() ? ($this->cosmetic_info['capeTexture'] ?? 'defaultCape') : 'defaultCape';
    }
}
