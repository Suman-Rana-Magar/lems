<?php

namespace App\Enums;

enum OrganizerRequestEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
