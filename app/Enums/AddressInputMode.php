<?php

namespace App\Enums;

enum AddressInputMode: string
{
    case CEP = 'cep';
    case MANUAL = 'manual';
}
