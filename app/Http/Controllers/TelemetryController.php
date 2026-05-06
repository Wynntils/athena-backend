<?php

namespace App\Http\Controllers;

use App\Http\Requests\TelemetryRequest;
use App\Models\GatheringSpot;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\JsonResponse;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
#[Group('Telemetry')]
class TelemetryController extends Controller
{
    #[ExcludeRouteFromDocs]
    public function sendGatheringSpot(TelemetryRequest $request): JsonResponse
    {
        $gatheringSpot = GatheringSpot::firstOrCreate(
            ['_id' => $request->validated('spot.x').':'.$request->validated('spot.y').':'.$request->validated('spot.z')],
            [
                'type' => $request->validated('spot.type'),
                'material' => $request->validated('spot.material'),
            ]
        );

        $gatheringSpot->users = collect($gatheringSpot->users)->push($request->user()->id)->unique()->all();
        $gatheringSpot->lastSeen = currentTimeMillis();

        $gatheringSpot->save();

        return response()->json(['message' => 'Gathering spot saved']);
    }
}
