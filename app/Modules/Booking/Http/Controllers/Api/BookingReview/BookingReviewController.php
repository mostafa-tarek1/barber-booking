<?php

namespace App\Modules\Booking\Http\Controllers\Api\BookingReview;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Domain\Enums\BookingReviewStatusEnum;
use App\Modules\Booking\Http\Requests\BookingReview\CreateBookingReviewRequest;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Booking\Infrastructure\Persistence\Models\BookingReview\BookingReview;
use App\Support\PhoneNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->input('limit', 12), 1), 30);
        $reviews = BookingReview::query()
            ->where('status', BookingReviewStatusEnum::APPROVED->value)
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(fn (BookingReview $review) => [
                'id' => $review->id,
                'name' => $review->publicName(),
                'rating' => $review->rating,
                'comment' => $review->comment,
                'created_at' => $review->created_at?->toISOString(),
            ]);

        return response()->json(['status' => 200, 'message' => 'Reviews fetched successfully', 'data' => $reviews]);
    }

    public function store(CreateBookingReviewRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $booking = Booking::query()->findOrFail($validated['booking_id']);
        $this->assertBookingPhone($booking, $validated['phone']);

        $review = BookingReview::query()->create([
            ...$validated,
            'phone' => PhoneNumber::normalize($validated['phone']),
            'status' => BookingReviewStatusEnum::PENDING,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'تم إرسال تقييمك للمراجعة، شكراً لك.',
            'data' => ['id' => $review->id, 'status' => $review->status->value],
        ], 201);
    }

    private function assertBookingPhone(Booking $booking, string $phone): void
    {
        if (PhoneNumber::normalize($booking->phone) !== PhoneNumber::normalize($phone)) {
            throw ValidationException::withMessages([
                'phone' => ['رقم الموبايل غير مطابق للرقم المستخدم في الحجز.'],
            ]);
        }
    }
}
