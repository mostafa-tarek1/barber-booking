<?php

namespace App\Modules\Notification\Application\Services;

use App\Modules\Base\Domain\ApiService\WhatsAppApiService;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Notification\Application\Jobs\SendWhatsAppMessage;
use App\Support\BusinessDay;

class BookingWhatsAppNotifier
{
    public function __construct(private readonly WhatsAppApiService $whatsApp) {}

    public function isConfigured(): bool
    {
        return $this->whatsApp->isConfigured();
    }

    public function queueCreatedBookingMessages(Booking $booking): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $this->loadMessageRelations($booking);
        $this->queueAdminNotification($booking);

        if ((int) $booking->status === BookingStatusEnum::CONFIRMED->value) {
            $this->queueCustomerConfirmation($booking);
        }
    }

    public function queueConfirmedBookingMessage(Booking $booking): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $this->loadMessageRelations($booking);
        $this->queueCustomerConfirmation($booking);
    }

    public function queueReminder(Booking $booking): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $this->loadMessageRelations($booking);
        $this->dispatch($booking->phone, $this->reminderMessage($booking));
    }

    private function queueAdminNotification(Booking $booking): void
    {
        $adminPhone = config('whatsapp.admin_phone');

        if (filled($adminPhone)) {
            $this->dispatch((string) $adminPhone, $this->adminMessage($booking));
        }
    }

    private function queueCustomerConfirmation(Booking $booking): void
    {
        if (filled($booking->phone)) {
            $this->dispatch($booking->phone, $this->confirmationMessage($booking));
        }
    }

    private function dispatch(string $phone, string $message): void
    {
        SendWhatsAppMessage::dispatch($phone, $message)->afterCommit();
    }

    private function loadMessageRelations(Booking $booking): void
    {
        $booking->loadMissing(['services', 'employee']);
    }

    private function adminMessage(Booking $booking): string
    {
        return "📅 حجز جديد #{$booking->id}\n"
            ."العميل: {$booking->name}\n"
            ."الهاتف: {$booking->phone}\n"
            ."الخدمة: {$this->serviceNames($booking)}\n"
            ."الموعد: {$this->appointmentLabel($booking)}\n"
            ."الحلاق: {$this->barberName($booking)}";
    }

    private function confirmationMessage(Booking $booking): string
    {
        return "أهلاً {$booking->name} 👋\n"
            .'تم تأكيد حجزك في '.config('app.name')." ✅\n"
            ."الخدمة: {$this->serviceNames($booking)}\n"
            ."الموعد: {$this->appointmentLabel($booking)}\n"
            ."الحلاق: {$this->barberName($booking)}\n"
            .'ننتظرك في الموعد.';
    }

    private function reminderMessage(Booking $booking): string
    {
        return "أهلاً {$booking->name} 👋\n"
            .'اقترب موعد حجزك في '.config('app.name')." ⏰\n"
            ."الخدمة: {$this->serviceNames($booking)}\n"
            ."الموعد: {$this->appointmentLabel($booking)}\n"
            .'يرجى الحضور إلى الصالون في الموعد.';
    }

    private function serviceNames(Booking $booking): string
    {
        return $booking->services
            ->map(fn ($service) => $service->pivot->name_snapshot ?: $service->name)
            ->filter()
            ->join('، ');
    }

    private function appointmentLabel(Booking $booking): string
    {
        $appointment = BusinessDay::appointmentAt(
            $booking->date->format('Y-m-d'),
            $booking->from_hour
        );

        return $appointment->format('Y-m-d').' الساعة '.$appointment->format('H:i');
    }

    private function barberName(Booking $booking): string
    {
        $firstName = $booking->employee?->first_name ?? '';
        $lastName = $booking->employee?->last_name ?? '';

        return trim("{$firstName} {$lastName}") ?: 'غير محدد';
    }
}
