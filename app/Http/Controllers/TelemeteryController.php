<?php

namespace App\Http\Controllers;

use App\Http\Requests\TelemeteryRequest;

class TelemeteryController extends Controller
{
    public function sendGatheringSpot(TelemeteryRequest $request) {
        return response()->json(['success' => true]);
    }
}
