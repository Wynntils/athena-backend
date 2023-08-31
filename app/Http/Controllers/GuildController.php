<?php

namespace App\Http\Controllers;
use App\Models\Guild;
use http\Env\Response;
use Illuminate\Http\Request;

class GuildController extends Controller {


    public function list($filter = "") {
        if($filter === "all") {
            return Guild::all()->toArray();
        }

        if($filter === "") {
            return Guild::all()->filter(function($guild) {
                return $guild->color !== null && $guild->color !== '';
            })->toArray();
        }
    }
    public function setColor(Request $request)
    {
        $guild = Guild::findOrFail($request->validated('guild'));
        $guild->color = $request->validated('color');
        $guild->save();
        return ['message' => 'Successfully updated guild.'];
    }

}
