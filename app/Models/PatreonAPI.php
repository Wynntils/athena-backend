<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;
use Patreon;

/**
 * Class PatreonAPI
 * @package App\Models
 * @property string $access_token
 * @property string $refresh_token
 * @property int $expires_in
 * @property string $scope
 * @property string $token_type
 * @property string $created_at
 * @property string $updated_at
 *
 * @mixin Builder
 */
class PatreonAPI extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'patreon_api';
    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_in',
        'scope',
        'token_type',
        'created_at',
        'updated_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'expires_in' => 'int',
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
