<?php

namespace App\Http\Enums;

enum GatheringMaterial: string
{
    // Woodcutting
    case OAK = "OAK";
    case BIRCH = "BIRCH";
    case WILLOW = "WILLOW";
    case ACACIA = "ACACIA";
    case SPRUCE = "SPRUCE";
    case JUNGLE = "JUNGLE";
    case DARK = "DARK";
    case LIGHT = "LIGHT";
    case PINE = "PINE";
    case AVO = "AVO";
    case SKY = "SKY";
    // Mining
    case COPPER = "COPPER";
    case GRANITE = "GRANITE";
    case GOLD = "GOLD";
    case SANDSTONE = "SANDSTONE";
    case IRON = "IRON";
    case SILVER = "SILVER";
    case COBALT = "COBALT";
    case KANDERSTONE = "KANDERSTONE";
    case DIAMOND = "DIAMOND";
    case MOLTEN = "MOLTEN";
    case VOIDSTONE = "VOIDSTONE";
    // Farming
    case WHEAT = "WHEAT";
    case BARLEY = "BARLEY";
    case OATS = "OATS";
    case MALT = "MALT";
    case HOPS = "HOPS";
    case RYE = "RYE";
    case MILLET = "MILLET";
    case DECAY_ROOTS = "DECAY_ROOTS";
    case RICE = "RICE";
    case SORGHUM = "SORGHUM";
    case HEMP = "HEMP";
    // Fishing
    case GUDGEON = "GUDGEON";
    case TROUT = "TROUT";
    case SALMON = "SALMON";
    case CARP = "CARP";
    case ICEFISH = "ICEFISH";
    case PIRANHA = "PIRANHA";
    case KOI = "KOI";
    case GYLIA_FISH = "GYLIA_FISH";
    case BASS = "BASS";
    case MOLTEN_EEL = "MOLTEN_EEL";
    case STARFISH = "STARFISH";
    // Global
    case DERNIC = "DERNIC";

}
