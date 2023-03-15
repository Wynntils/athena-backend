<?php

namespace App\Models;

use App\Enums\AccountType;
use App\Models\Embedded\CosmeticInfo;
use App\Models\Embedded\DiscordInfo;
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
 * @property DiscordInfo|null $discordInfo
 * @property CosmeticInfo|null $cosmeticInfo
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

    public function updateAccount($username, $version): void
    {
        $this->authToken = \Str::uuid()->toString();
        $this->lastActivity = currentTimeMillis();
        $this->username = $username;

        $this->latestVersion = $version;

        $usedVersions = $this->usedVersions;
        $usedVersions[$version] = currentTimeMillis();
        $this->usedVersions = $usedVersions;

        $this->save();
    }

    public function updateDiscord($id, $username): void
    {
        $this->discordInfo()->create([
            'id' => $id,
            'username' => $username,
        ]);
    }

    public function getConfigs(): ArrayObject
    {
        $files = new ArrayObject();
        $configs = Storage::disk('configs');
        foreach ($configs->files($this->id) as $file) {
            $files[basename($file)] = zlib_decode($configs->get($file));
        }

        return $files;
    }

    public function uploadConfig(UploadedFile $file): bool
    {
        if (in_array($this->id, config('athena.debug.users'))) {
            \Log::info('Uploading config for user ' . $this->id, ['file' => $file->getClientOriginalName()]);
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
            return basename($file);
        })->toArray();
    }
}
