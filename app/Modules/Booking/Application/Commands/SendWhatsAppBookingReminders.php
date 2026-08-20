<?php

namespace App\Modules\Booking\Application\Commands;

use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Notification\Application\Services\BookingWhatsAppNotifier;
use App\Support\BusinessDay;
use Carbon\CarbonInterface;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SendWhatsAppBookingReminders extends Command
{
    protected $signature = 'bookings:send-whatsapp-reminders';

    protected $description = 'Queue WhatsApp reminders for upcoming confirmed bookings';

    public function handle(BookingWhatsAppNotifier $notifier): int
    {
        if (! $notifier->isConfigured()) {
            return self::SUCCESS;
        }

        $now = now(config('app.timezone'));
        $cutoff = $now->copy()->addMinutes((int) config('whatsapp.reminder_minutes', 60));

        $this->reminderCandidates($now, $cutoff)->chunkById(100, function ($bookings) use ($notifier, $now, $cutoff) {
            foreach ($bookings as $booking) {
                $this->queueIfDue($booking, $notifier, $now, $cutoff);
            }
        });

        return self::SUCCESS;
    }

    private function reminderCandidates(CarbonInterface $now, CarbonInterface $cutoff): Builder
    {
        return Booking::query()
            ->where('status', BookingStatusEnum::CONFIRMED->value)
            ->whereNull('whatsapp_reminder_sent_at')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereBetween('date', [
                $now->copy()->subDay()->toDateString(),
                $cutoff->toDateString(),
            ]);
    }

    private function queueIfDue(
        Booking $booking,
        BookingWhatsAppNotifier $notifier,
        CarbonInterface $now,
        CarbonInterface $cutoff
    ): void {
        $appointment = BusinessDay::appointmentAt($booking->date->toDateString(), $booking->from_hour);

        if ($appointment->lte($now) || $appointment->gt($cutoff)) {
            return;
        }

        $this->claimAndQueue($booking, $notifier);
    }

    private function claimAndQueue(Booking $booking, BookingWhatsAppNotifier $notifier): void
    {
        DB::transaction(function () use ($booking, $notifier) {
            $claimed = Booking::query()
                ->whereKey($booking->id)
                ->where('status', BookingStatusEnum::CONFIRMED->value)
                ->whereNull('whatsapp_reminder_sent_at')
                ->update(['whatsapp_reminder_sent_at' => now()]);

            if ($claimed === 1) {
                $notifier->queueReminder($booking);
            }
        });
    }
}
