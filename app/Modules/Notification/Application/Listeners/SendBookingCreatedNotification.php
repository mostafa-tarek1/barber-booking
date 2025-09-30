<?php

namespace App\Modules\Notification\Application\Listeners;

use App\Modules\Notification\Application\Events\BookingCreated;
use App\Modules\Notification\Application\Services\NotificationService;
use App\Modules\Notification\Domain\Enums\NotificationTypeEnum;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBookingCreatedNotification
{

    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(BookingCreated $event): void
    {
        try {
            $booking = $event->booking;
            $services = $event->services;
            $employeeId = $event->employeeId;

            // إنشاء رسالة الإشعار
            $serviceNames = collect($services)->pluck('name')->join(', ');
            $title = 'حجز جديد';
            $message = "تم إنشاء حجز جديد من العميل {$booking->name} لخدمة {$serviceNames} في {$booking->date->format('Y-m-d')} الساعة {$booking->from_hour}";

            // إرسال إشعار للموظف
            $this->notificationService->sendToEmployee(
                $employeeId,
                NotificationTypeEnum::BOOKING_CREATED,
                $title,
                $message,
                [
                    'booking_id' => $booking->id,
                    'customer_name' => $booking->name,
                    'customer_phone' => $booking->phone,
                    'customer_email' => $booking->email,
                    'service_names' => $serviceNames,
                    'date' => $booking->date->format('Y-m-d'),
                    'time' => $booking->from_hour,
                    'duration' => $booking->to_hour,
                ]
            );

            // إرسال إشعار للمديرين
            $this->notificationService->sendToAllEmployees(
                NotificationTypeEnum::BOOKING_CREATED,
                $title,
                $message,
                [
                    'booking_id' => $booking->id,
                    'customer_name' => $booking->name,
                    'customer_phone' => $booking->phone,
                    'customer_email' => $booking->email,
                    'service_names' => $serviceNames,
                    'date' => $booking->date->format('Y-m-d'),
                    'time' => $booking->from_hour,
                    'duration' => $booking->to_hour,
                    'employee_id' => $employeeId,
                ]
            );

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send booking created notification: ' . $e->getMessage(), [
                'booking_id' => $event->booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
