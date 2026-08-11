<?php

use App\Modules\Booking\Http\Controllers\Api\Booking\BookingController;
use App\Modules\Booking\Http\Controllers\Api\BookingReview\BookingReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::post('create_booking', [BookingController::class, 'createBooking'])
        ->middleware('throttle:public-booking-create');
    Route::post('check_availability', [BookingController::class, 'checkAvailability'])
        ->middleware('throttle:public-booking-read');
    Route::post('available_slots', [BookingController::class, 'availableSlots'])
        ->middleware('throttle:public-booking-read');
    Route::post('fetch_reviews', [BookingReviewController::class, 'index'])
        ->middleware('throttle:public-booking-read');
    Route::post('create_review', [BookingReviewController::class, 'store'])
        ->middleware('throttle:public-booking-create');
});
