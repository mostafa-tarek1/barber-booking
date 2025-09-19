<?php

use App\Modules\Booking\Http\Controllers\Api\Booking\BookingController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
        // ---------------- Bookings ----------------
        Route::post('create_booking', [BookingController::class, 'createBooking']);
});
