<?php

namespace App\Modules\Booking\Domain\Enums;

use App\Modules\Base\Domain\Traits\HasMetadata;

enum BookingStatusEnum: int
{
    use HasMetadata;
    case PENDING = 0;
    case CONFIRMED = 1;
    case CANCELLED = 2;

    public function getMetadata(): array
    {
        return match ($this) {
            self::PENDING => [
                'label' => 'Pending',
                'value' => 0
            ],
            self::CONFIRMED => [
                'label' => 'Confirmed',
                'value' => 1
            ],
            self::CANCELLED => [
                'label' => 'Cancelled',
                'value' => 2
            ],
            default => [
                'label' => 'Unknown',
                'value' => 4
            ]
        };
    }

}
