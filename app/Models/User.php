<?php

namespace App\Models;

use App\Http\Enums\AccountType;
use ArrayObject;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $username
 * @property string $authToken
 * @property AccountType $accountType
 * @property DiscordInfo $discordInfo
 * @property CosmeticInfo $cosmeticInfo
 *
 * @mixin Builder
 */
class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;

    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        '_id',
        'accountType',
        'username',
        'password',
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'authToken',
        'remember_token',
    ];

    protected $casts = [
        'accountType' => AccountType::class,
    ];

    public function discordInfo(): \Jenssegers\Mongodb\Relations\EmbedsOne
    {
        return $this->embedsOne(DiscordInfo::class);
    }

    public function cosmeticInfo(): \Jenssegers\Mongodb\Relations\EmbedsOne
    {
        return $this->embedsOne(CosmeticInfo::class);
    }

    public function updateAccount($username, $version): string
    {
        $this->authToken = \Str::uuid()->toString();
        $this->lastActivity = currentTimeMillis();
        $this->username = $username;

        $this->latestVersion = $version;

        $usedVersions = $this->usedVersions;
        $usedVersions[$version] = currentTimeMillis();
        $this->usedVersions = $usedVersions;

        $this->save();

        return $this->authToken;
    }

    public function updateDiscord($id, $username): void
    {
        $this->discordInfo = [
            'id' => $id,
            'username' => $username,
        ];

        $this->save();
    }

    public function getConfigs(): ArrayObject
    {
        $files = new ArrayObject();
        $configs = Storage::disk('configs');
        foreach ($configs->files($this->id) as $file) {
            $files[basename($file)] = json_encode(json_decode(zlib_decode($configs->get($file))));
        }

        return $files;
    }

    public function uploadConfig(UploadedFile $file): bool
    {
        return Storage::disk('configs')->put($this->id.'/'.$file->getClientOriginalName(), zlib_encode(file_get_contents($file), ZLIB_ENCODING_DEFLATE));
    }

    public function getConfigAmount(): int
    {
        return count(Storage::disk('configs')->files($this->id));
    }

    public function deleteConfig(string $file): bool
    {
        $configs = Storage::disk('configs');

        if (!$configs->exists($this->id.'/'.$file)) {
            return false;
        }

        $configs->delete($this->id.'/'.$file);

        return true;
    }

    public function getConfig(string $file): ?string
    {
        $configs = Storage::disk('configs');

        if (!$configs->exists($this->id.'/'.$file)) {
            return null;
        }

        return json_encode(json_decode(zlib_decode($configs->get($this->id.'/'.$file))));
    }

    public function getConfigFiles(): array
    {
        $configs = Storage::disk('configs');

        return collect($configs->files($this->id))->map(function ($file) {
            return explode('/', $file)[1];
        })->toArray();
    }
}
