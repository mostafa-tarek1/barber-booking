<?php

use App\Modules\Booking\Http\Controllers\Api\Booking\BookingController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::post('create_booking', [BookingController::class, 'createBooking'])
        ->middleware('throttle:public-booking-create');
    Route::post('check_availability', [BookingController::class, 'checkAvailability'])
        ->middleware('throttle:public-booking-read');
    Route::post('available_slots', [BookingController::class, 'availableSlots'])
        ->middleware('throttle:public-booking-read');
});
