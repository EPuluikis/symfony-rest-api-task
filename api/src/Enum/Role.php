<?php

namespace App\Enum;

use App\Trait\EnumValues;

enum Role: string
{
    use EnumValues;

    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_USER = 'ROLE_USER';
}
