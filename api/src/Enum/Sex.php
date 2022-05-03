<?php

namespace App\Enum;

use App\Trait\EnumValues;

enum Sex: string
{
    use EnumValues;

    case MALE = 'MALE';
    case FEMALE = 'FEMALE';
    case OTHER = 'OTHER';
}
