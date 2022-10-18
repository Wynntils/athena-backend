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
        // Cache this for 5 minutes
        $isArtemis = str($request->userAgent())->contains('Artemis');
        $client = $isArtemis ? 'Artemis' : 'Wynntils';

        $releases = $this->getReleases($client, $stream);

        $latest = $releases->first();

        if (!$latest) {
            return response()->json(['error' => 'No release found for this stream'], 404);
        }

        $cache = \Cache::get('version', []);
        if (!isset($cache[$stream][$client])) {
            $cache[$stream][$client] = [];
            $cache = $this->updateCache($cache, $stream, $client, $latest);
        }

        if ($cache[$stream][$client]['tag'] !== $latest['tag_name']) {
            $cache = $this->updateCache($cache, $stream, $client, $latest);
        }

        return response()->json([
            'version' => $latest['tag_name'],
            'url' => $latest['assets'][0]['browser_download_url'],
            'md5' => $cache[$stream][$client]['md5'],
            'changelog' => route('version.changelog', [$latest['tag_name']]),
        ]);
    }

    public function changelog(Request $request, $version)
    {
        $isArtemis = str($request->userAgent())->contains('Artemis');
        $client = $isArtemis ? 'Artemis' : 'Wynntils';
        $stream = str($version)->contains('beta') ? 'ce' : 're';

        $releases = $this->getReleases($client, $version === 'latest' ? 'all' : $stream);
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

    private function getReleases($repo, $stream): \Illuminate\Support\Collection
    {
        // Cache this for 5 minutes
        $cache = \Cache::remember('releases.'.$repo, 300, function () use ($repo) {
            return collect($this->github->repo()->releases()->all('Wynntils', $repo));
        });

        if ($stream === 'all') {
            return $cache;
        }

        return $cache->filter(function ($release) use ($stream) {
            return match ($stream) {
                're' => $release['prerelease'] === false,
                default => true,
            };
        });
    }

    private function updateCache(mixed $cache, $stream, string $client, mixed $latest)
    {
        $cache[$stream][$client] = [
            'tag' => $latest['tag_name'],
            'md5' => md5_file($latest['assets'][0]['browser_download_url']),
        ];

        \Cache::put('version', $cache);

        return $cache;
    }
}
