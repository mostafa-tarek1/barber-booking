<?php

namespace Tests\Unit;

use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use Tests\TestCase;

class BookingNotesTest extends TestCase
{
    public function test_customer_notes_reach_the_booking_model(): void
    {
        $bookingData = BookingDTO::fromArray(['notes' => 'يفضل التواصل عبر واتساب'])->toArray();
        $booking = (new Booking)->fill($bookingData);

        $this->assertSame('يفضل التواصل عبر واتساب', $booking->notes);
    }
}
