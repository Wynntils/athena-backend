<?php

namespace App\Http\Controllers;
use App\Models\Guild;
use http\Env\Response;
use Illuminate\Http\Request;

class GuildController extends Controller {
    public function setColor(Request $request)
    {
        $guild = Guild::findOrFail($request->validated('guild'));
        $guild->color = $request->validated('color');
        $guild->save();
        return ['message' => 'Successfully updated guild.'];
    }

}
