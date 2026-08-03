<?php

namespace Tests\Feature;

use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Application\Services\BookingDepositService;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingDepositTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_deposit_is_recorded_on_the_booking(): void
    {
        $booking = Booking::query()->create(['name' => 'عميل اختبار', 'status' => 1]);
        $bookingDTO = BookingDTO::fromArray([
            'deposit_amount' => 150,
            'deposit_payment_method' => 'wallet',
        ]);

        app(BookingDepositService::class)->collect($booking, $bookingDTO, 30000);

        $this->assertDatabaseHas('booking_deposits', [
            'booking_id' => $booking->id,
            'payment_method' => 'wallet',
            'amount' => 150,
        ]);
    }

    public function test_deposit_above_booking_total_is_rejected(): void
    {
        $booking = Booking::query()->create(['name' => 'عميل اختبار', 'status' => 1]);
        $bookingDTO = BookingDTO::fromArray([
            'deposit_amount' => 301,
            'deposit_payment_method' => 'cash',
        ]);

        $this->expectException(ValidationException::class);

        app(BookingDepositService::class)->collect($booking, $bookingDTO, 30000);
    }
}
