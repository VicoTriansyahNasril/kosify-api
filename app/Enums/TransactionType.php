<?php

namespace App\Enums;

enum TransactionType: string
{
    case RENT = 'rent';
    case ELECTRICITY = 'electricity';
    case WATER = 'water';
    case DEPOSIT = 'deposit';
    case FINE = 'fine';
}