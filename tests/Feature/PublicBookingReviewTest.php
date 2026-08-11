<?php

namespace Tests\Feature;

use App\Modules\Booking\Domain\Enums\BookingReviewStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Booking\Infrastructure\Persistence\Models\BookingReview\BookingReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicBookingReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_phone_must_match_before_review_is_accepted(): void
    {
        $booking = $this->booking('01012345678');

        $this->postJson('/api/create_review', $this->reviewPayload($booking, '01100000000'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('phone');

        $this->assertDatabaseCount('booking_reviews', 0);
    }

    public function test_new_review_waits_for_approval_and_is_not_public(): void
    {
        $booking = $this->booking('01012345678');

        $this->postJson('/api/create_review', $this->reviewPayload($booking, $booking->phone))
            ->assertCreated()
            ->assertJsonPath('data.status', BookingReviewStatusEnum::PENDING->value);

        $this->postJson('/api/fetch_reviews')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    }

    public function test_public_reviews_include_first_name_only(): void
    {
        BookingReview::query()->create([
            'name' => 'أحمد محمد السيد',
            'phone' => '01012345678',
            'rating' => 5,
            'comment' => 'الخدمة ممتازة والمكان جميل جداً.',
            'status' => BookingReviewStatusEnum::APPROVED,
        ]);

        $this->postJson('/api/fetch_reviews')
            ->assertSuccessful()
            ->assertJsonPath('data.0.name', 'أحمد')
            ->assertJsonMissing(['phone' => '01012345678']);
    }

    private function booking(string $phone): Booking
    {
        return Booking::query()->create([
            'name' => 'عميل الموقع',
            'phone' => $phone,
            'date' => '2026-08-12',
            'from_hour' => '13:00',
            'to_hour' => '13:30',
        ]);
    }

    private function reviewPayload(Booking $booking, string $phone): array
    {
        return [
            'booking_id' => $booking->id,
            'name' => 'عميل الموقع',
            'phone' => $phone,
            'rating' => 5,
            'comment' => 'الخدمة ممتازة والمكان جميل جداً.',
        ];
    }
}
