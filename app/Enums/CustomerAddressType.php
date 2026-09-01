<?php

namespace App\Enums;

enum CustomerAddressType: string
{
    case HOME = 'home';
    case WORK = 'work';
    case OTHER = 'other';
}
