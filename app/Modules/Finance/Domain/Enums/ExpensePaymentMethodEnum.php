<?php

namespace App\Modules\Finance\Domain\Enums;

enum ExpensePaymentMethodEnum: string
{
    case Cash = 'cash';
    case Wallet = 'wallet';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'نقدي',
            self::Wallet => 'محفظة إلكترونية',
        };
    }
}
