<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case UNPAID = 'unpaid';
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
}