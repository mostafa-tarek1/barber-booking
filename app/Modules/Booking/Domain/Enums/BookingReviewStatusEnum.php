<?php

namespace App\Modules\Booking\Domain\Enums;

enum BookingReviewStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
