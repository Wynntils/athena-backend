<?php

namespace App\Http\Libraries\Requests\Cache;

use App\Http\Enums\ProfessionType;
use App\Models\GatheringSpot;

class GatheringSpots implements CacheContract
{

    public function refreshRate(): int
    {
        return 3600;
    }

    public function generate(): array
    {
        $result = [];
        $woodCutting = &$result['woodCutting'];
        $mining = &$result['mining'];
        $farming = &$result['farming'];
        $fishing = &$result['fishing'];

        $spots = GatheringSpot::all();
        foreach ($spots as $spot) {
            /** @var GatheringSpot $spot */
            $reliability = $spot->calculateReliability();

            if ($reliability === 0 || $spot->shouldRemove()) {
                $spot->delete();
                continue;
            }

            if ($reliability < 50) {
                continue;
            }

            $obj = [];

            $obj["type"] = $spot->material;
            $obj["lastSeen"] = $spot->lastSeen;
            $obj["reliability"] = $reliability;

            $location = &$obj['location'];
            [$x, $y, $z] = $spot->getLocation();
            $location['x'] = $x;
            $location['y'] = $y;
            $location['z'] = $z;

            match ($spot->type) {
                ProfessionType::WOODCUTTING => $woodCutting[] = $obj,
                ProfessionType::MINING => $mining[] = $obj,
                ProfessionType::FISHING => $fishing[] = $obj,
                ProfessionType::FARMING => $farming[] = $obj,
                default => null,
            };
        }

        return $result;
    }
}

