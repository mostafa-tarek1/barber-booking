<?php

namespace App\Modules\Booking\Http\Controllers\Dashboard\BookingReview;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Domain\Enums\BookingReviewStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\BookingReview\BookingReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(BookingReviewStatusEnum::class)],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $reviews = BookingReview::query()
            ->with('booking:id,date,from_hour')
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['search'] ?? null, fn ($query, $search) => $query->where(
                fn ($searchQuery) => $searchQuery
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
            ))
            ->latest('id')
            ->paginate(15);

        return response()->json(['status' => 200, 'message' => 'Reviews fetched successfully', 'data' => $reviews]);
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'review_id' => ['required', 'integer', 'exists:booking_reviews,id'],
            'status' => ['required', Rule::enum(BookingReviewStatusEnum::class)],
        ]);
        $review = BookingReview::query()->findOrFail($validated['review_id']);
        $review->update(['status' => $validated['status']]);

        return response()->json(['status' => 200, 'message' => 'Review status updated', 'data' => $review]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'review_id' => ['required', 'integer', 'exists:booking_reviews,id'],
        ]);
        BookingReview::query()->findOrFail($validated['review_id'])->delete();

        return response()->json(['status' => 200, 'message' => 'Review deleted successfully']);
    }
}
