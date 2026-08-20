<?php

namespace App\Modules\Notification\Application\Listeners;

use App\Modules\Notification\Application\Events\BookingConfirmed;
use App\Modules\Notification\Application\Services\BookingWhatsAppNotifier;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendBookingConfirmedWhatsAppNotification
{
    public function __construct(private readonly BookingWhatsAppNotifier $notifier) {}

    public function handle(BookingConfirmed $event): void
    {
        // A notification outage must not roll back a successful status change.
        try {
            $this->notifier->queueConfirmedBookingMessage($event->booking);
        } catch (Throwable $exception) {
            Log::error('Failed to queue booking-confirmed WhatsApp message', [
                'booking_id' => $event->booking->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
