<?php

use App\Modules\Booking\Http\Controllers\Dashboard\Booking\BookingController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->group(function () {
    Route::middleware('baseAuthMiddleware:employee')->group(function () {
        // ---------------- Bookings ----------------
        Route::post('fetch_bookings', [BookingController::class, 'fetchBooking']);
        Route::post('fetch_booking_details', [BookingController::class, 'fetchBookingDetails']);
        Route::post('create_booking', [BookingController::class, 'createBooking']);
        Route::post('update_booking', [BookingController::class, 'updateBooking']);
        Route::post('delete_booking', [BookingController::class, 'deleteBooking']);
    });
});
