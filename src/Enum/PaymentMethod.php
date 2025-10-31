<?php

namespace App\Enum;

enum PaymentMethod: string
{
    case PAYPAL = 'paypal';
    case GOOGLE = 'google';
    case CREDIT_CARD = 'credit_card';
    case APPLE = 'apple';
    case BANK_TRANSFER = 'bank_transfer';
}
