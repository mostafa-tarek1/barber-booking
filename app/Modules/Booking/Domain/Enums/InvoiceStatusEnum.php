<?php

namespace App\Modules\Booking\Domain\Enums;

enum InvoiceStatusEnum: string
{
    case UNPAID = 'unpaid';
    case PARTIALLY_PAID = 'partially_paid';
    case PAID = 'paid';
    case VOID = 'void';
}
