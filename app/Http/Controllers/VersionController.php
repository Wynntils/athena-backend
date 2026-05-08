<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChangelogBetweenResource;
use App\Http\Resources\VersionResource;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Dedoc\Scramble\Attributes\Group;
use GrahamCampbell\GitHub\GitHubManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

#[Group('Version')]
class VersionController extends Controller
{
    public function __construct(public GitHubManager $github) {}

    private function userAgentDetails(Request $request)
    {
        // Semver User Agent: Wynntils Artemis\\v0.0.4+MC-1.20.2 (client) FABRIC
        // BETA User Agent : Wynntils Artemis\\v0.0.4-beta.71+MC-1.20.2 (client) FABRIC
        // DEV User Agent: Wynntils Artemis\\v0.0.4-SNAPSHOT+MC-1.20.2 (dev) FABRIC
        $userAgent = str($request->userAgent())->lower();

        $client = 'Artemis';
        $dev = $userAgent->contains('dev');
        $modloader = $mcVersion = null;

        $modloader = $this->normalizeModloader((string) $userAgent->afterLast(' '));
        if ($userAgent->contains('+')) {
            $mcVersion = (string) $userAgent->upper()->after('+MC-')->before(' ');
        }

        return [
            'client' => $client,
            'dev' => $dev,
            'modloader' => $modloader,
            'mcVersion' => $mcVersion,
            'stream' => $this->userAgentStream($userAgent),
        ];
    }

    /**
     * Get the latest release for a stream
     */
    public function latest(Request $request, $stream): VersionResource|JsonResponse
    {
        [
            'client' => $client,
            'dev' => $dev,
            'modloader' => $modloader,
            'mcVersion' => $mcVersion,
            'stream' => $userAgentStream,
        ] = $this->userAgentDetails($request);

        $requestedMcVersion = $mcVersion ? strtolower((string) $mcVersion) : null;

        $stream = $this->resolveLatestStream($stream, $userAgentStream);
        $releases = $this->getReleases($client, $stream, 1, $requestedMcVersion);

        $latest = $releases->first(function ($release) {
            return ! empty($release['assets']); // Filter out releases without assets
        });

        if (! $latest) {
            return response()->json(['error' => 'No release found for this stream'], 404)
                ->header('Vary', 'User-Agent');
        }

        $cacheMcVersion = (string) ($requestedMcVersion ?? 'unknown');
        $assetMd5 = $this->getCachedAssetMd5($client, $stream, $cacheMcVersion, $latest);

        $asset = collect($latest['assets'])->first(function ($asset) use ($modloader, $requestedMcVersion) {
            $name = str($asset['name']);

            if ($this->isSourceJar($name)) {
                return false;
            }

            if (! $name->contains($modloader)) {
                return false;
            }

            if (! $requestedMcVersion) {
                return true;
            }

            return $this->assetMinecraftVersion($asset['name']) === $requestedMcVersion;
        });

        if (! $asset) {
            return response()->json(['error' => 'No release found for this stream'], 404)
                ->header('Vary', 'User-Agent');
        }

        $latestTag = str($latest['tag_name']);

        $response = [
            'version' => (string) $latestTag,
            'url' => $asset['browser_download_url'],
            'md5' => $assetMd5[$asset['name']] ?? null,
            'changelog' => route('version.changelog', [$latest['tag_name']]),
        ];

        if (str($asset['name'])->contains('+MC-')) {
            $tagMcVersion = (string) str($asset['name'])->after('+MC-')->before('.jar');
            $response['supportedMcVersion'] = $tagMcVersion;
        }

        return (new VersionResource($response))->response()->header('Vary', 'User-Agent');
    }

    #[ExcludeRouteFromDocs]
    public function changelog(Request $request, $version): JsonResponse
    {
        ['client' => $client] = $this->userAgentDetails($request);
        $stream = str($version)->contains(['alpha', 'beta']) ? 'ce' : 're';

        $releases = $this->getReleases($client, $stream);
        if ($version === 'latest') {
            $version = $releases->first()['tag_name'];
        }

        $release = $releases->firstWhere('tag_name', $version);

        if (! $release) {
            return response()->json(['error' => 'No release found for this version'], 404);
        }

        // clean changelog body of markdown links and commit hashes
        $changelog = str($release['body'])->replaceMatches('/\[(.*?)\]\(.*?\)/', '$1');
        $changelog = str($changelog)->replaceMatches('/\([0-9a-f]{7}\)/', '');
        // replace crlf with lf
        $changelog = str($changelog)->replace("\r\n", "\n");

        return response()->json([
            'version' => $release['tag_name'],
            'changelog' => $changelog,
        ]);
    }

    #[ExcludeRouteFromDocs]
    public function download($version, $stream, $modloader = 'fabric'): RedirectResponse|JsonResponse
    {
        $client = 'Artemis';
        $modloader = $this->normalizeModloader(strtolower($modloader));
        $releases = $this->getReleases($client, $stream);

        $latest = $releases->first();

        if (! $latest) {
            return response()->json(['error' => 'No release found for this version'], 404);
        }

        $asset = collect($latest['assets'])->first(function ($asset) use ($modloader) {
            $name = (string) $asset['name'];

            return ! $this->isSourceJar($name)
                && str_contains(strtolower($name), strtolower((string) $modloader));
        });

        if (! $asset) {
            return response()->json(['error' => 'No release found for this version'], 404);
        }

        return response()->redirectTo($asset['browser_download_url']);
    }

    private function normalizeModloader(?string $modloader): ?string
    {
        return match (strtolower((string) $modloader)) {
            'forge' => 'neoforge',
            default => $modloader,
        };
    }

    private function assetMinecraftVersion(string $assetName): ?string
    {
        if (! preg_match('/\+MC-([0-9]+(?:\.[0-9]+)*)/i', $assetName, $matches)) {
            return null;
        }

        return strtolower($matches[1]);
    }

    private function userAgentStream(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'pre-alpha') => 'pre-alpha',
            str_contains($userAgent, 'beta') || str_contains($userAgent, '-beta') => 'beta',
            str_contains($userAgent, ' alpha') || str_contains($userAgent, '-alpha') => 'alpha',
            str_contains($userAgent, ' rc') || str_contains($userAgent, '-rc') => 'rc',
            default => 'release',
        };
    }

    private function resolveLatestStream(string $requestedStream, string $userAgentStream): string
    {
        $normalized = strtolower((string) $requestedStream);

        if (str_starts_with($normalized, 'v')) {
            $normalized = substr($normalized, 1);
        }

        return match ($normalized) {
            '', 'latest', 'ce' => $userAgentStream ?: 'release',
            're' => 'release',
            'pre-alpha', 'alpha', 'beta', 'rc', 'release' => $normalized,
            default => 'release',
        };
    }

    /**
     * Get changelogs between two versions
     */
    public function changelogBetween(Request $request, $fromQuery, $toQuery): ChangelogBetweenResource|JsonResponse
    {
        ['client' => $client] = $this->userAgentDetails($request);

        // Determine the release stream (pre-releases vs. stable)
        $stream = str($fromQuery)->contains(['alpha', 'beta']) ? 'ce' : 're';
        $releases = $this->getReleases($client, $stream);

        // Find the "from" and "to" releases, with pagination fallback
        $from = $releases->firstWhere('tag_name', $fromQuery);
        $to = $releases->firstWhere('tag_name', $toQuery);

        if (! $from || ! $to) {
            $page = 1;
            do {
                $search = $this->getReleases($client, $stream, ++$page);
                if (! $from) {
                    $from = $search->firstWhere('tag_name', $fromQuery);
                }
                if (! $to) {
                    $to = $search->firstWhere('tag_name', $toQuery);
                }
                $releases = $releases->merge($search);
            } while ((! $from || ! $to) && $search->count() > 0);

            if (! $from || ! $to) {
                match (true) {
                    ! $from && ! $to => $error = 'No releases found for these versions',
                    ! $from => $error = 'No release found for the from version',
                    ! $to => $error = 'No release found for the to version',
                };

                return response()->json(['error' => $error], 404);
            }
        }

        // Reverse releases so we iterate oldest → newest
        $releases = $releases->reverse();

        $perVersionChangelogs = [];

        foreach ($releases as $release) {
            $tag = $release['tag_name'];

            // Skip until after the "from" version
            if (version_compare($tag, $from['tag_name'], '<=')) {
                continue;
            }

            // Stop after including the "to" version
            if (version_compare($tag, $to['tag_name'], '>')) {
                break;
            }

            // Clean and normalize the changelog body
            $body = str($release['body'])
                ->replaceMatches('/\[(.*?)\]\(.*?\)/', '$1')
                ->replaceMatches('/\([0-9a-f]{7}\)/', '')
                ->replace("\r\n", "\n")
                ->value();

            // Store the full body as a single string per version
            $perVersionChangelogs[$tag] = $body;
        }

        return new ChangelogBetweenResource([
            'from' => $from['tag_name'],
            'to' => $to['tag_name'],
            'changelogs' => $perVersionChangelogs,
        ]);
    }

    private function getReleases($repo, $stream, $page = 1, $mcVersion = null): \Illuminate\Support\Collection
    {
        $stream = $this->normalizeReleaseStream($stream);

        if (! in_array($stream, [
            'ce', 'pre-alpha', 'alpha', 'beta', 'rc', 'release',
        ])) {
            throw new \InvalidArgumentException('Invalid stream');
        }

        // Cache this for 5 minutes
        try {
            $cache = \Cache::remember('releases.'.$repo.'-'.$page, 300, function () use ($page, $repo) {
                return collect($this->github->repo()->releases()->all('Wynntils', $repo, [
                    'per_page' => 100, // 100 is the maximum
                    'page' => $page,
                ]));
            });
            \Cache::put('releases.'.$repo.'.backup', $cache);
        } catch (\Exception $e) {
            $cache = \Cache::get('releases.'.$repo.'.backup', collect());
        }

        // filter then return in semver order
        return $cache->filter(function ($release) use ($mcVersion, $stream) {
            // filter out releases that don't match the mcVersion
            // the +MC- prefix is used to indicate that the release is for a specific mcVersion
            // this prefix is found in the assets name
            if ($mcVersion) {
                $requestedMcVersion = strtolower((string) $mcVersion);

                $assets = collect($release['assets'])->filter(function ($asset) use ($requestedMcVersion) {
                    $name = (string) $asset['name'];

                    if ($this->isSourceJar($name)) {
                        return false;
                    }

                    return $this->assetMinecraftVersion($name) === $requestedMcVersion;
                });
                if ($assets->isEmpty()) {
                    return false;
                }
            }

            return $release['draft'] === false && match ($stream) {
                'release' => $release['prerelease'] === false,
                'ce' => $release['prerelease'] === true,
                'pre-alpha' => $release['prerelease'] === true && str_contains($release['tag_name'], 'pre-alpha'),
                'alpha' => $release['prerelease'] === true && str_contains($release['tag_name'], 'alpha') && ! str_contains($release['tag_name'], 'pre-alpha'),
                'beta' => $release['prerelease'] === true && str_contains($release['tag_name'], 'beta'),
                'rc' => $release['prerelease'] === true && str_contains($release['tag_name'], 'rc'),
            } && ! str($release['tag_name'])->upper()->contains('+MC-');
        })->sort(function ($a, $b) {
            return version_compare($b['tag_name'], $a['tag_name']);
        });
    }

    private function normalizeReleaseStream(string $stream): string
    {
        return match (strtolower($stream)) {
            're', 'latest' => 'release',
            default => strtolower($stream),
        };
    }

    private function getCachedAssetMd5(string $client, string $stream, string $mcVersion, array $latest): array
    {
        $stream = $this->normalizeReleaseStream($stream);
        $tag = strtolower((string) $latest['tag_name']);
        $cacheKey = "version.md5.{$client}.{$stream}.{$mcVersion}.{$tag}";

        return \Cache::remember($cacheKey, 300, function () use ($latest) {
            $md5 = [];

            foreach ($latest['assets'] as $asset) {
                $md5[$asset['name']] = md5_file($asset['browser_download_url']);
            }

            return $md5;
        });
    }

    private function isSourceJar(string $assetName): bool
    {
        return str_ends_with(strtolower($assetName), '-sources.jar');
    }
}
