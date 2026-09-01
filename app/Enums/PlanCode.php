<?php

namespace App\Enums;

enum PlanCode: string
{
    case BASIC = 'basic';
    case MEDIUM = 'medium';
    case PLUS = 'plus';
    case PREMIUM = 'premium';
}

