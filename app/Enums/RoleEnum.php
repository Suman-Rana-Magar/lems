<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case NORMAL = 'normal';
    case ORGANIZER = 'organizer';
}
