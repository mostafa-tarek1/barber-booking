<?php

use App\Modules\Booking\Http\Controllers\Dashboard\Booking\BookingController;
use App\Modules\Booking\Http\Controllers\Dashboard\Invoice\InvoiceController;
use App\Modules\Booking\Http\Controllers\Dashboard\SalonCustomer\SalonCustomerController;
use App\Modules\Booking\Http\Controllers\Dashboard\BookingReview\BookingReviewController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->group(function () {
    Route::middleware('baseAuthMiddleware:employee')->group(function () {
        // ---------------- Bookings ----------------
        Route::post('fetch_bookings', [BookingController::class, 'fetchBooking']);
        Route::post('fetch_booking_details', [BookingController::class, 'fetchBookingDetails']);
        Route::post('check_availability', [BookingController::class, 'checkAvailability']);
        Route::post('available_slots', [BookingController::class, 'availableSlots']);
        Route::post('fetch_invoices', [InvoiceController::class, 'index']);
        Route::post('fetch_invoice_details', [InvoiceController::class, 'show']);

        Route::middleware('employeeRole:admin,super_admin,barber')->group(function () {
            Route::post('fetch_barber_time_offs', [BookingController::class, 'fetchTimeOffs']);
            Route::post('fetch_barber_sales_report', [InvoiceController::class, 'barberSalesReport']);
        });

        Route::middleware('employeeRole:admin,super_admin,employee')->group(function () {
            Route::post('fetch_salon_customers', [SalonCustomerController::class, 'index']);
            Route::post('create_booking', [BookingController::class, 'createBooking']);
            Route::post('update_booking', [BookingController::class, 'updateBooking']);
            Route::post('delete_booking', [BookingController::class, 'deleteBooking']);
            Route::post('create_invoice', [InvoiceController::class, 'create']);
            Route::post('collect_invoice_payment', [InvoiceController::class, 'collectPayment']);
        });

        Route::middleware('employeeRole:admin,super_admin')->group(function () {
            Route::post('fetch_booking_reviews', [BookingReviewController::class, 'index']);
            Route::post('update_booking_review_status', [BookingReviewController::class, 'updateStatus']);
            Route::post('delete_booking_review', [BookingReviewController::class, 'destroy']);
            Route::post('create_barber_time_off', [BookingController::class, 'createTimeOff']);
            Route::post('delete_barber_time_off', [BookingController::class, 'deleteTimeOff']);
            Route::post('fetch_barber_working_hours', [BookingController::class, 'fetchWorkingHours']);
            Route::post('update_barber_working_hours', [BookingController::class, 'updateWorkingHours']);
        });
    });
});
