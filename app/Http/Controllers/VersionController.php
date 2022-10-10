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
        $isArtemis = str($request->userAgent())->contains('Artemis');

        $releases = $this->getReleases($isArtemis ? 'Artemis' : 'Wynntils', $stream);

        $latest = $releases->first();

        if (!$latest) {
            return response()->json(['error' => 'No release found for this stream'], 404);
        }

        return response()->json([
            'version' => $latest['tag_name'],
            'url' => $latest['assets'][0]['browser_download_url'],
            'commit' => $latest['target_commitish'],
            'changelog' => $latest['body'],
        ]);
    }

    private function getReleases($repo, $stream): \Illuminate\Support\Collection
    {
        return collect($this->github->repo()->releases()->all('Wynntils', $repo))->filter(function ($release) use ($stream) {
            return $release['prerelease'] === ($stream === 'ce');
        });
    }
}
