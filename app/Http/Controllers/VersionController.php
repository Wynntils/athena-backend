<?php

namespace App\Http\Controllers;

use GrahamCampbell\GitHub\GitHubManager;
use Illuminate\Http\Request;

class VersionController extends Controller
{
    public function __construct(public GitHubManager $github)
    {
    }

    private function userAgentDetails(Request $request) {
        // Legacy User Agent: Wynntils\\1.15.1-10 (client)
        // Semver User Agent: Wynntils Artemis\\v0.0.4-beta.71+MC-1.20.2 (client) FABRIC
        // DEV User Agent: Wynntils Artemis\\v0.0.4-SNAPSHOT+MC-1.20.2 (dev) FABRIC
        $userAgent = str($request->userAgent())->lower();

        $isLegacy = $userAgent->startsWith('wynntils\\');
        $client = $isLegacy ? 'Wynntils' : 'Artemis';
        $dev = $userAgent->contains('dev');
        $modloader = $mcVersion = null;

        // Artemis only
        if (!$isLegacy) {
            $modloader = $userAgent->afterLast(' ');
            if ($userAgent->contains('+')) {
                $mcVersion = $userAgent->upper()->after('+MC-')->before(' ');
            }
        }

        return [
            'isLegacy' => $isLegacy,
            'client' => $client,
            'dev' => $dev,
            'modloader' => $modloader,
            'mcVersion' => $mcVersion,
        ];
    }

    public function latest(Request $request, $stream)
    {
        if (str_starts_with($stream, 'v')) {
            $stream = str_replace('v', '', $stream);
        }

        [
            'isLegacy' => $isLegacy,
            'client' => $client,
            'dev' => $dev,
            'modloader' => $modloader,
            'mcVersion' => $mcVersion
        ] = $this->userAgentDetails($request);

        $releases = $this->getReleases($client, $stream, 1, $mcVersion ?? null);

        $latest = $releases->first(function ($release) {
            return !empty($release['assets']); // Filter out releases without assets
        });

        if (!$latest) {
            return response()->json(['error' => 'No release found for this stream'], 404);
        }

        $mcVersion = (string) ($mcVersion ?? 'unknown');

        $cache = \Cache::get('version', []);

        if (
            !isset($cache[$stream][$mcVersion][$client])
            || $cache[$stream][$mcVersion][$client]['tag'] !== $latest['tag_name']
            || !is_array($cache[$stream][$mcVersion][$client]['md5'])
            || empty($cache[$stream][$mcVersion][$client]['md5'])
        ) {
            $cache[$stream][$mcVersion][$client] = [];
            $cache = $this->updateCache($cache, $stream, $mcVersion, $client, $latest);
        }

        // We need to filter the asset list to only the current modloader if we're using Artemis
        if ($isLegacy) {
            $asset = $latest['assets'][0]; // Legacy Wynntils only has one asset
        } else {
            $asset = collect($latest['assets'])->first(function ($asset) use ($modloader) {
                return str($asset['name'])->contains($modloader);
            });
        }

        if (!$asset) {
            return response()->json(['error' => 'No release found for this stream'], 404);
        }

        $latestTag = str($latest['tag_name']);

        $response = [
            'version' => $latestTag,
            'url' => $asset['browser_download_url'],
            'md5' => $cache[$stream][$mcVersion][$client]['md5'][$asset['name']],
            'changelog' => route('version.changelog', [$latest['tag_name']]),
        ];


        if (!$isLegacy && str($asset['name'])->contains('+MC-')) {
            $tagMcVersion = str($asset['name'])->after('+MC-')->before('.jar');
            $response['supportedMcVersion'] = $tagMcVersion;
        }

        return response()->json($response);
    }

    public function changelog(Request $request, $version)
    {
        ['client' => $client] = $this->userAgentDetails($request);
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

    public function changelogBetween(Request $request, $fromQuery, $toQuery)
    {
        ['client' => $client] = $this->userAgentDetails($request);

        $stream = str($fromQuery)->contains(['alpha', 'beta']) ? 'ce' : 're';

        $releases = $this->getReleases($client, $stream);

        $from = $releases->firstWhere('tag_name', $fromQuery);
        $to = $releases->firstWhere('tag_name', $toQuery);

        if (!$from || !$to) {
            $page = 1;
            // Check the next page of releases if we didn't find the release
            do {
                $search = $this->getReleases($client, $stream, ++$page);
                if (!$from) {
                    $from = $search->firstWhere('tag_name', $fromQuery);
                }
                if (!$to) {
                    $to = $search->firstWhere('tag_name', $toQuery);
                }
                $releases = $releases->merge($search);
            } while ((!$from || !$to) && $search->count() > 0);

            if (!$from || !$to) {
                match (true) {
                    !$from && !$to => $error = 'No releases found for these versions',
                    !$from => $error = 'No release found for the from version',
                    !$to => $error = 'No release found for the to version',
                };

                return response()->json(['error' => $error], 404);
            }
        }

        // Reverse the releases so we can iterate from the oldest to the newest
        $releases = $releases->reverse();

        // We want to collate all the changelog headings and their bodies
        $changelog = [];
        $header = null;

        foreach ($releases as $release) {
            if (version_compare($release['tag_name'], $from['tag_name'], '<=')) {
                continue;
            }

            // clean changelog body of markdown links and commit hashes
            $release['body'] = str($release['body'])->replaceMatches('/\[(.*?)\]\(.*?\)/', '$1');
            $release['body'] = str($release['body'])->replaceMatches('/\([0-9a-f]{7}\)/', '');
            // replace crlf with lf
            $release['body'] = str($release['body'])->replace("\r\n", "\n");

            // Split the changelog into lines
            $lines = explode("\n", $release['body']);

            // Iterate over the lines and collate the headings and their bodies
            // Headers start with ### and are followed by a space
            foreach ($lines as $line) {
                $line = str($line)->trim();
                if ($line->isEmpty()) {
                    continue;
                }
                if ($line->startsWith('## ')) {
                    continue;
                }
                if ($line->startsWith('### ')) {
                    $header = str($line)->replace('### ', '')->value();
                    if (!isset($changelog[$header])) {
                        $changelog[$header] = [];
                    }
                } else {
                    $line = $line->value();
                    if(empty($header)){
                        continue;
                    }
                    // check if value is already in array
                    if (!in_array($line, $changelog[$header])) {
                        $changelog[$header][] = $line;
                    }
                }
            }

            if ($release['tag_name'] === $to['tag_name']) {
                break;
            }
        }

        // Join the changelog headings and their bodies into a single string
        //  - Headings should be in a specific order
        $changelog = collect($changelog)->sortKeysUsing(function ($a, $b) {
                $order = [
                    'New Features',
                    'Bug Fixes',
                    'Performance Improvements',
                    'Reverts',
                    'Documentation',
                    'Styles',
                    'Miscellaneous Chores',
                    'Code Refactoring',
                    'Tests',
                    'Build System',
                    'Continuous Integration',
                ];
                $aIndex = array_search($a, $order);
                $bIndex = array_search($b, $order);

                return $aIndex <=> $bIndex;
            })->map(function ($body, $header) {
                return "### $header \n" . implode("\n", $body);
            })->implode("\n\n");

        // Add the version range to the top of the changelog
        $changelog = "## Changelog from $from[tag_name] to $to[tag_name] \n\n" . $changelog;

        return response()->json([
            'from' => $from['tag_name'],
            'to' => $to['tag_name'],
            'changelog' => $changelog,
        ]);
    }

    public function changelogBetweenV2(Request $request, $fromQuery, $toQuery)
    {
        ['client' => $client] = $this->userAgentDetails($request);

        // Determine the release stream (pre-releases vs. stable)
        $stream = str($fromQuery)->contains(['alpha', 'beta']) ? 'ce' : 're';
        $releases = $this->getReleases($client, $stream);

        // Find the "from" and "to" releases, with pagination fallback
        $from = $releases->firstWhere('tag_name', $fromQuery);
        $to   = $releases->firstWhere('tag_name', $toQuery);

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
                    ! $from          => $error = 'No release found for the from version',
                    ! $to            => $error = 'No release found for the to version',
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

        return response()->json([
            'from'       => $from['tag_name'],
            'to'         => $to['tag_name'],
            'changelogs' => $perVersionChangelogs,
        ]);
    }


    private function getReleases($repo, $stream, $page = 1, $mcVersion = null): \Illuminate\Support\Collection
    {
        if (!in_array($stream, [
            're', 'latest', 'ce', // legacy versioning
            'pre-alpha', 'alpha', 'beta', 'rc', 'release', // semver versioning
        ])) {
            throw new \InvalidArgumentException('Invalid stream');
        }

        // Cache this for 5 minutes
        /** @var \Illuminate\Support\Collection $cache */
        try {
            $cache = \Cache::remember('releases.' . $repo . '-' . $page, 300, function () use ($page, $repo) {
                return collect($this->github->repo()->releases()->all('Wynntils', $repo, [
                    'per_page' => 100, // 100 is the maximum
                    'page' => $page,
                ]));
            });
            \Cache::put('releases.' . $repo . '.backup', $cache);
        } catch (\Exception $e) {
            $cache = \Cache::get('releases.' . $repo . '.backup', collect());
        }

        // filter then return in semver order
        return $cache->filter(function ($release) use ($mcVersion, $stream) {
            // filter out releases that don't match the mcVersion
            // the +MC- prefix is used to indicate that the release is for a specific mcVersion
            // this prefix is found in the assets name
            if ($mcVersion) {
                $assets = collect($release['assets'])->filter(function ($asset) use ($mcVersion) {
                    return str_contains($asset['name'], '+MC-') && str_contains($asset['name'], $mcVersion);
                });
                if ($assets->isEmpty()) {
                    return false;
                }
            }
            return $release['draft'] === false && match ($stream) {
                'release', 're', 'latest' => $release['prerelease'] === false,
                'pre-alpha' => $release['prerelease'] === true && str_contains($release['tag_name'], 'pre-alpha'),
                'alpha' => $release['prerelease'] === true && str_contains($release['tag_name'], 'alpha') && !str_contains($release['tag_name'], 'pre-alpha'),
                'beta' => $release['prerelease'] === true && str_contains($release['tag_name'], 'beta'),
                'rc' => $release['prerelease'] === true && str_contains($release['tag_name'], 'rc'),
                default => true,
            } && !str($release['tag_name'])->upper()->contains('+MC-');
        })->sort(function ($a, $b) {
            return version_compare($b['tag_name'], $a['tag_name']);
        });
    }

    private function updateCache(mixed $cache, $stream, string $mcVersion, string $client, mixed $latest)
    {
        $md5 = [];

        foreach($latest['assets'] as $key => $asset) {
            $md5[$asset['name']] = md5_file($asset['browser_download_url']);
        }

        $cache[$stream][$mcVersion][$client] = [
            'tag' => $latest['tag_name'],
            'md5' => $md5,
        ];

        \Cache::put('version', $cache);

        return $cache;
    }
}
