<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuildRequest;
use App\Models\Guild;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;

class GuildController extends Controller
{
    #[ExcludeRouteFromDocs]
    public function setColor(GuildRequest $request): array
    {
        $guild = Guild::findOrFail($request->validated('guild'));
        $guild->color = $request->validated('color');
        $guild->save();

        return ['message' => 'Successfully updated guild.'];
    }
}
