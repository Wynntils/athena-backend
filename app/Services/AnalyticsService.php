<?php

namespace App\Services;

use App\Models\User;
use Br33f\Ga4\MeasurementProtocol\Dto\Event\BaseEvent;
use Br33f\Ga4\MeasurementProtocol\Dto\Request\BaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    private const SKIP_PREFIXES = [
        'telescope',
        'horizon',
        'api/docs',
        'docs',
        'oauth',
        'auth',
        'crash',
        'phpinfo',
    ];

    public function shouldSkip(Request $request): bool
    {
        $path = $request->path();
        foreach (self::SKIP_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public function resolveUserId(Request $request): ?string
    {
        if ($request->hasHeader('authToken')) {
            $authToken = $request->header('authToken');

            if (empty($authToken)) {
                return null;
            }

            $cacheKey = "analytics_user:{$authToken}";

            // Use get/put instead of remember so that null is properly cached.
            if (($cached = Cache::get($cacheKey, '__MISS__')) !== '__MISS__') {
                return $cached;
            }

            $userId = User::where('auth_token', $authToken)->select('id')->first()?->id;
            Cache::put($cacheKey, $userId, 300);

            return $userId;
        }

        /** Internal API calls use the apiKey route parameter as a stable identity. */
        return $request->route('apiKey');
    }

    public function resolveClientId(Request $request, ?string $userId): string
    {
        return $userId ?? md5($request->ip().$request->userAgent());
    }

    /**
     * @return array{wynntils_version: string|null, mc_version: string|null, modloader: string|null}
     */
    public function parseUserAgent(string $rawUa): array
    {
        $ua = str($rawUa)->lower();

        if (! $ua->startsWith('wynntils artemis')) {
            return ['wynntils_version' => null, 'mc_version' => null, 'modloader' => null];
        }

        $wynntilsVersion = null;
        $mcVersion = null;
        $rawModloader = (string) $ua->afterLast(' ');
        $modloader = in_array($rawModloader, ['fabric', 'forge', 'neoforge']) ? $rawModloader : null;

        if ($ua->contains('+')) {
            $wynntilsVersion = (string) str($rawUa)->after('\\v')->before('+') ?: null;
            $mcVersion = (string) str($rawUa)->after('+MC-')->before(' ')->lower() ?: null;
        }

        return [
            'wynntils_version' => $wynntilsVersion,
            'mc_version' => $mcVersion,
            'modloader' => $modloader,
        ];
    }

    public function buildPageViewRequest(Request $request): BaseRequest
    {
        $userId = $this->resolveUserId($request);
        $clientId = $this->resolveClientId($request, $userId);

        $baseRequest = new BaseRequest;
        $baseRequest->setClientId($clientId);

        if ($userId !== null) {
            $baseRequest->setUserId($userId);
        }

        $pageViewEvent = new BaseEvent('page_view');
        $pageViewEvent->setParamValue('page_title', $request->path());
        $pageViewEvent->setParamValue('page_location', $request->fullUrl());
        $pageViewEvent->setParamValue('page_path', $request->path());
        $pageViewEvent->setParamValue('engagement_time_msec', '1');

        $ua = $this->parseUserAgent($request->userAgent() ?? '');

        foreach (['wynntils_version', 'mc_version', 'modloader'] as $param) {
            if ($ua[$param] !== null) {
                $pageViewEvent->setParamValue($param, $ua[$param]);
            }
        }

        $baseRequest->addEvent($pageViewEvent);

        return $baseRequest;
    }

    public function buildCapeSubmittedRequest(string $username): BaseRequest
    {
        $baseRequest = new BaseRequest;
        $baseRequest->setClientId(md5($username));

        $capeEvent = new BaseEvent('cape_submitted');
        $capeEvent->setParamValue('username', $username);
        $capeEvent->setParamValue('engagement_time_msec', '1');

        $baseRequest->addEvent($capeEvent);

        return $baseRequest;
    }
}
