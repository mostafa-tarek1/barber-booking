<?php

namespace App\Modules\Notification\Application\Listeners;

use App\Modules\Notification\Application\Events\BookingCreated;
use App\Modules\Notification\Application\Services\BookingWhatsAppNotifier;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendBookingCreatedWhatsAppNotification
{
    public function __construct(private readonly BookingWhatsAppNotifier $notifier) {}

    public function handle(BookingCreated $event): void
    {
        // A notification outage must not roll back a successfully created booking.
        try {
            $this->notifier->queueCreatedBookingMessages($event->booking);
        } catch (Throwable $exception) {
            Log::error('Failed to queue booking-created WhatsApp messages', [
                'booking_id' => $event->booking->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
