<?php

use App\Modules\Booking\Http\Controllers\Dashboard\Booking\BookingController;
use App\Modules\Booking\Http\Controllers\Dashboard\Invoice\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->group(function () {
    Route::middleware('baseAuthMiddleware:employee')->group(function () {
        // ---------------- Bookings ----------------
        Route::post('fetch_bookings', [BookingController::class, 'fetchBooking']);
        Route::post('fetch_booking_details', [BookingController::class, 'fetchBookingDetails']);
        Route::post('create_booking', [BookingController::class, 'createBooking']);
        Route::post('update_booking', [BookingController::class, 'updateBooking']);
        Route::post('delete_booking', [BookingController::class, 'deleteBooking']);
        Route::post('check_availability', [BookingController::class, 'checkAvailability']);
        Route::post('available_slots', [BookingController::class, 'availableSlots']);
        Route::post('fetch_barber_time_offs', [BookingController::class, 'fetchTimeOffs']);
        Route::post('create_barber_time_off', [BookingController::class, 'createTimeOff']);
        Route::post('delete_barber_time_off', [BookingController::class, 'deleteTimeOff']);
        Route::post('create_invoice', [InvoiceController::class, 'create']);
        Route::post('fetch_invoices', [InvoiceController::class, 'index']);
        Route::post('fetch_invoice_details', [InvoiceController::class, 'show']);
        Route::post('fetch_barber_sales_report', [InvoiceController::class, 'barberSalesReport']);
    });
});
