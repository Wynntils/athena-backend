<?php

namespace App\Enums;

enum CosmeticSlot: string
{
    case BACK = 'back';
    case LEFT_ARM = 'left_arm';
    case RIGHT_ARM = 'right_arm';
    case LEFT_LEG = 'left_leg';
    case RIGHT_LEG = 'right_leg';
    case HEAD = 'head';
}
