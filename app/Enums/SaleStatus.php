<?php

namespace App\Enums;

enum SaleStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Cancelled = 'cancelled';
}
