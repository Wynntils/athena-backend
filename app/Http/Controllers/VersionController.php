<?php

namespace App\Http\Controllers;

use GrahamCampbell\GitHub\GitHubManager;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    public function __construct(public GitHubManager $github)
    {
    }

    public function latest(Request $request, $stream)
    {
        $userAgent = str($request->userAgent())->lower();
        $isArtemis = $userAgent->contains('artemis');
        $client = $isArtemis ? 'Artemis' : 'Wynntils';
        // Useragent example: Wynntils Artemis\1.1.0-513 (client) FABRIC
        // Get the modloder from the useragent
        // The modloader is the last word in the useragent
        if ($isArtemis) {
            $modloader = $userAgent->afterLast(' ');
        }

        $releases = $this->getReleases($client, $stream);

        $latest = $releases->first(function ($release) {
            return !empty($release['assets']); // Filter out releases without assets
        });

        if (!$latest) {
            return response()->json(['error' => 'No release found for this stream'], 404);
        }

        $cache = \Cache::get('version', []);
        if (
            !isset($cache[$stream][$client])
            || $cache[$stream][$client]['tag'] !== $latest['tag_name']
            || !is_array($cache[$stream][$client]['md5'])
            || empty($cache[$stream][$client]['md5'])
        ) {
            $cache[$stream][$client] = [];
            $cache = $this->updateCache($cache, $stream, $client, $latest);
        }

        // We need to filter the asset list to only the current modloader if we're using Artemis
        if ($isArtemis) {
            $asset = collect($latest['assets'])->first(function ($asset) use ($modloader) {
                return str($asset['name'])->contains($modloader);
            });
        } else {
            $asset = $latest['assets'][0]; // Legacy Wynntils only has one asset
        }

        return response()->json([
            'version' => $latest['tag_name'],
            'url' => $asset['browser_download_url'],
            'md5' => $cache[$stream][$client]['md5'][$asset['name']],
            'changelog' => route('version.changelog', [$latest['tag_name']]),
        ]);
    }

    public function changelog(Request $request, $version)
    {
        $userAgent = str($request->userAgent())->lower();
        $isArtemis = $userAgent->contains('artemis');
        $client = $isArtemis ? 'Artemis' : 'Wynntils';
        $stream = str($version)->contains(['alpha', 'beta']) ? 'ce' : 're';

        $releases = $this->getReleases($client, $stream);
        if ($version === 'latest') {
            $version = $releases->first()['tag_name'];
        }

        $release = $releases->firstWhere('tag_name', $version);

        if (!$release) {
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

    public function download($version, $stream, $modloader = 'fabric')
    {
        $client = $version === "legacy" ? 'Wynntils' : 'Artemis';
        $isArtemis = $client === 'Artemis';
        $releases = $this->getReleases($client, $stream);

        $latest = $releases->first();

        if (!$latest) {
            return response()->json(['error' => 'No release found for this version'], 404);
        }

        if ($isArtemis) {
            $asset = collect($latest['assets'])->first(function ($asset) use ($modloader) {
                return str($asset['name'])->contains($modloader);
            });
        } else {
            $asset = $latest['assets'][0]; // Legacy Wynntils only has one asset
        }

        return response()->redirectTo($asset['browser_download_url']);
    }

    private function getReleases($repo, $stream): \Illuminate\Support\Collection
    {
        // Cache this for 5 minutes
        /** @var \Illuminate\Support\Collection $cache */
        try {
            $cache = \Cache::remember('releases.' . $repo, 300, function () use ($repo) {
                return collect($this->github->repo()->releases()->all('Wynntils', $repo));
            });
            \Cache::put('releases.' . $repo . '.backup', $cache);
        } catch (\Exception $e) {
            $cache = \Cache::get('releases.' . $repo . '.backup', collect());
        }

        // filter then return in semver order
        return $cache->filter(function ($release) use ($stream) {
            return $release['draft'] === false && match ($stream) {
                're', 'latest' => $release['prerelease'] === false,
                default => true,
            };
        })->sort(function ($a, $b) {
            return version_compare($b['tag_name'], $a['tag_name']);
        });
    }

    private function updateCache(mixed $cache, $stream, string $client, mixed $latest)
    {
        $md5 = [];

        foreach($latest['assets'] as $key => $asset) {
            $md5[$asset['name']] = md5_file($asset['browser_download_url']);
        }

        $cache[$stream][$client] = [
            'tag' => $latest['tag_name'],
            'md5' => $md5,
        ];

        \Cache::put('version', $cache);

        return $cache;
    }
}
