<?php

namespace App\Modules\Booking\Application\Services;

use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Booking\Infrastructure\Persistence\Models\BookingDeposit\BookingDeposit;
use Illuminate\Validation\ValidationException;

class BookingDepositService
{
    public function collect(Booking $booking, BookingDTO $bookingDTO, int $bookingTotalCents): ?BookingDeposit
    {
        if ($bookingDTO->deposit_amount === null) {
            return null;
        }

        $depositCents = (int) round((float) $bookingDTO->deposit_amount * 100);
        if ($depositCents > $bookingTotalCents) {
            throw ValidationException::withMessages([
                'deposit_amount' => 'لا يمكن أن يكون الديبوزيت أكبر من إجمالي الحجز.',
            ]);
        }

        return $booking->deposit()->create([
            'collected_by' => $bookingDTO->created_by,
            'payment_method' => $bookingDTO->deposit_payment_method,
            'amount' => $depositCents / 100,
            'collected_at' => now(),
        ]);
    }
}
