<?php

namespace App\Enums;

enum CosmeticStatus: string
{
    case QUEUED   = 'queued';
    case APPROVED = 'approved';
    case BANNED   = 'banned';
}
