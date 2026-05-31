<?php

namespace App\Enums;

enum UnitStatus: string
{
    case Ready = 'ready';
    case Sold = 'sold';
    case Returned = 'returned';
}
