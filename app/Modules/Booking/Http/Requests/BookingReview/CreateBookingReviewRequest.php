<?php

namespace App\Modules\Booking\Http\Requests\BookingReview;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id', 'unique:booking_reviews,booking_id'],
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }
}
