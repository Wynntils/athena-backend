<?php

namespace App\Http\Enums;

enum ProfessionType
{

    case WOODCUTTING;
    case MINING;
    case FISHING;
    case FARMING;
    case ALCHEMISM;
    case ARMOURING;
    case COOKING;
    case JEWELING;
    case SCRIBING;
    case TAILORING;
    case WEAPONSMITHING;
    case WOODWORKING;
    case OVERALL;

    public function icon(): string
    {
        return match ($this) {
            self::WOODCUTTING => "Ⓒ",
            self::MINING => "Ⓑ",
            self::FISHING => "Ⓚ",
            self::FARMING => "Ⓙ",
            self::ALCHEMISM => "Ⓛ",
            self::ARMOURING => "Ⓗ",
            self::COOKING => "Ⓐ",
            self::JEWELING => "Ⓓ",
            self::SCRIBING => "Ⓔ",
            self::TAILORING => "Ⓕ",
            self::WEAPONSMITHING => "Ⓖ",
            self::WOODWORKING => "Ⓘ",
            self::OVERALL => "",
        };
    }

    public function leaderboard(): string
    {
        return match ($this) {
            self::WOODCUTTING => "solo/woodcutting",
            self::MINING => "solo/mining",
            self::FISHING => "solo/fishing",
            self::FARMING => "solo/farming",
            self::ALCHEMISM => "solo/alchemism",
            self::ARMOURING => "solo/armouring",
            self::COOKING => "solo/cooking",
            self::JEWELING => "solo/jeweling",
            self::SCRIBING => "solo/scribing",
            self::TAILORING => "solo/tailoring",
            self::WEAPONSMITHING => "solo/weaponsmithing",
            self::WOODWORKING => "solo/woodworking",
            self::OVERALL => "overall/all",
        };
    }
}
