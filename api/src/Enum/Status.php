<?php

namespace App\Enum;

use App\Trait\EnumValues;

enum Status: string
{
    use EnumValues;

    case WAITING = 'WAITING';
    case COMPLETED = 'COMPLETED';
    case REFUNDED = 'REFUNDED';
}
