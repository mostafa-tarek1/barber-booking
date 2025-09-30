<?php

namespace App\Modules\Notification\Application\Listeners;

use App\Modules\Notification\Application\Events\BookingConfirmed;
use App\Modules\Notification\Application\Services\NotificationService;
use App\Modules\Notification\Domain\Enums\NotificationTypeEnum;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBookingConfirmedNotification
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
  public function handle(BookingConfirmed $event): void
  {
    try {
      $booking = $event->booking;

      // جلب تفاصيل الحجز
      $booking->load(['services', 'employee']);

      $serviceNames = $booking->services->pluck('name')->join(', ');
      $title = 'تم تأكيد الحجز';
      $message = "تم تأكيد حجزك لخدمة {$serviceNames} في {$booking->date->format('Y-m-d')} الساعة {$booking->from_hour}";

      // إرسال إشعار للعميل (إذا كان لديه حساب)
      if ($booking->user_id) {
        $this->notificationService->sendToUser(
          $booking->user_id,
          NotificationTypeEnum::BOOKING_CONFIRMED,
          $title,
          $message,
          [
            'booking_id' => $booking->id,
            'service_names' => $serviceNames,
            'date' => $booking->date->format('Y-m-d'),
            'time' => $booking->from_hour,
            'duration' => $booking->to_hour,
            'employee_name' => $booking->employee ? trim($booking->employee->first_name . ' ' . $booking->employee->last_name) : null,
          ]
        );
      }

      // إرسال إشعار للموظف
      if ($booking->employee_id) {
        $this->notificationService->sendToEmployee(
          $booking->employee_id,
          NotificationTypeEnum::BOOKING_CONFIRMED,
          'حجز مؤكد',
          "تم تأكيد حجز العميل {$booking->name} لخدمة {$serviceNames}",
          [
            'booking_id' => $booking->id,
            'customer_name' => $booking->name,
            'customer_phone' => $booking->phone,
            'service_names' => $serviceNames,
            'date' => $booking->date->format('Y-m-d'),
            'time' => $booking->from_hour,
            'duration' => $booking->to_hour,
          ]
        );
      }

    } catch (\Exception $e) {
      \Illuminate\Support\Facades\Log::error('Failed to send booking confirmed notification: ' . $e->getMessage(), [
        'booking_id' => $event->booking->id,
        'error' => $e->getMessage()
      ]);
    }
  }
}
