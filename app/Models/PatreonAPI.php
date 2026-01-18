<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Patreon;

/**
 * Class PatreonAPI
 * @package App\Models
 * @property int $id
 * @property string $access_token
 * @property string $refresh_token
 * @property int $expires_in
 * @property string $scope
 * @property string $token_type
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class PatreonAPI extends Model
{
    protected $table = 'patreon_api';

    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_in',
        'scope',
        'token_type',
    ];

    protected $casts = [
        'expires_in' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function getApi(): Patreon\API
    {
        $oauth = new Patreon\OAuth(config('services.patreon.client_id'), config('services.patreon.client_secret'));

        // Check if we have an access token
        $api = self::first();
        if (!$api) {
            // If not, get one
            $tokens = $oauth->refresh_token(config('services.patreon.refresh_token'), null);
            $api = self::create($tokens);
        } else if ($api->updated_at->addSeconds($api->expires_in)->isPast()) {
            // If it is, refresh it
            $tokens = $oauth->refresh_token($api->refresh_token, null);
            $api->update($tokens);
        } else {
            // If it's still valid, use it
            $tokens = $api->toArray();
        }

        if (isset($tokens['error'])) {
            throw new \RuntimeException($tokens['error']);
        }

        // Oauth with Patreon API
        return new Patreon\API($api->access_token);
    }
}
