<?php

namespace App\Http\Controllers;
use App\Models\Guild;
use Illuminate\Http\Request;

class GuildController extends Controller {


    public function list() {
        return Guild::all()->toArray();
    }
    public function setColor(Request $request)
    {
        $guild = Guild::findOrFail($request->validated('guild'));
        $guild->color = $request->validated('color');
        $guild->save();
        return ['message' => 'Successfully updated guild.'];
    }

}
