<?php

namespace App\Enums;

enum AccountType: string
{
    case NORMAL = 'NORMAL';
    case BANNED = 'BANNED';
    case DONATOR = 'DONATOR';
    case CONTENT_TEAM = 'CONTENT_TEAM';
    case HELPER = 'HELPER';
    case MODERATOR = 'MODERATOR';
}
