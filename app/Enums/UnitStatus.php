<?php

namespace App\Enums;

enum UnitStatus: string
{
    case Pending = 'pending';
    case Ready = 'ready';
    case Sold = 'sold';
    case Returned = 'returned';
}
