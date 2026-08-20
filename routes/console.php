<?php

use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    Booking::query()
        ->where('status', BookingStatusEnum::PENDING->value)
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->update([
            'status' => BookingStatusEnum::CANCELLED->value,
            'expires_at' => null,
            'updated_at' => now(),
        ]);
})->everyMinute()->name('expire-pending-bookings')->withoutOverlapping();

Schedule::command('bookings:send-whatsapp-reminders')
    ->everyMinute()
    ->name('send-whatsapp-booking-reminders')
    ->withoutOverlapping(5);
