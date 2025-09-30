<?php

namespace App\Modules\Notification\Application\Services;

use App\Modules\Notification\Domain\Enums\NotificationTypeEnum;
use App\Modules\Notification\Infrastructure\Persistence\Models\Notification\Notification;
use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Auth\Infrastructure\Persistence\Models\Customer\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * إرسال إشعار لموظف
     */
    public function sendToEmployee(
        int $employeeId,
        NotificationTypeEnum $type,
        string $title,
        string $message,
        array $data = [],
        ?int $createdBy = null
    ): Notification {
        return $this->createNotification([
            'employee_id' => $employeeId,
            'type' => $type->value,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'created_by' => $createdBy
        ]);
    }

    /**
     * إرسال إشعار لمستخدم
     */
    public function sendToUser(
        int $userId,
        NotificationTypeEnum $type,
        string $title,
        string $message,
        array $data = [],
        ?int $createdBy = null
    ): Notification {
        return $this->createNotification([
            'user_id' => $userId,
            'type' => $type->value,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'created_by' => $createdBy
        ]);
    }

    /**
     * إرسال إشعار لجميع الموظفين
     */
    public function sendToAllEmployees(
        NotificationTypeEnum $type,
        string $title,
        string $message,
        array $data = [],
        ?int $createdBy = null
    ): array {
        $employees = Employee::where('status', 'active')->get();
        $notifications = [];

        foreach ($employees as $employee) {
            $notifications[] = $this->sendToEmployee(
                $employee->id,
                $type,
                $title,
                $message,
                $data,
                $createdBy
            );
        }

        return $notifications;
    }

    /**
     * إرسال إشعار لجميع المستخدمين
     */
    public function sendToAllUsers(
        NotificationTypeEnum $type,
        string $title,
        string $message,
        array $data = [],
        ?int $createdBy = null
    ): array {
        $users = User::all();
        $notifications = [];

        foreach ($users as $user) {
            $notifications[] = $this->sendToUser(
                $user->id,
                $type,
                $title,
                $message,
                $data,
                $createdBy
            );
        }

        return $notifications;
    }

    /**
     * إرسال إشعار عام (للموظفين والمستخدمين)
     */
    public function sendBroadcast(
        NotificationTypeEnum $type,
        string $title,
        string $message,
        array $data = [],
        ?int $createdBy = null
    ): array {
        return array_merge(
            $this->sendToAllEmployees($type, $title, $message, $data, $createdBy),
            $this->sendToAllUsers($type, $title, $message, $data, $createdBy)
        );
    }

    /**
     * إنشاء إشعار جديد
     */
    private function createNotification(array $data): Notification
    {
        try {
            return Notification::create($data);
        } catch (\Exception $e) {
            Log::error('Failed to create notification: ' . $e->getMessage(), [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * تحديد إشعار كمقروء
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if (!$notification) {
            return false;
        }

        $notification->markAsRead();
        return true;
    }

    /**
     * تحديد إشعار كغير مقروء
     */
    public function markAsUnread(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if (!$notification) {
            return false;
        }

        $notification->markAsUnread();
        return true;
    }

    /**
     * تحديد جميع إشعارات موظف كمقروءة
     */
    public function markAllAsReadForEmployee(int $employeeId): int
    {
        return Notification::forEmployee($employeeId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * تحديد جميع إشعارات مستخدم كمقروءة
     */
    public function markAllAsReadForUser(int $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * حذف إشعار
     */
    public function deleteNotification(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);

        if (!$notification) {
            return false;
        }

        return $notification->delete();
    }

    /**
     * حذف جميع الإشعارات المقروءة لموظف
     */
    public function deleteReadNotificationsForEmployee(int $employeeId): int
    {
        return Notification::forEmployee($employeeId)
            ->read()
            ->delete();
    }

    /**
     * حذف جميع الإشعارات المقروءة لمستخدم
     */
    public function deleteReadNotificationsForUser(int $userId): int
    {
        return Notification::forUser($userId)
            ->read()
            ->delete();
    }

    /**
     * الحصول على عدد الإشعارات غير المقروءة لموظف
     */
    public function getUnreadCountForEmployee(int $employeeId): int
    {
        return Notification::forEmployee($employeeId)->unread()->count();
    }

    /**
     * الحصول على عدد الإشعارات غير المقروءة لمستخدم
     */
    public function getUnreadCountForUser(int $userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    /**
     * الحصول على إشعارات موظف
     */
    public function getNotificationsForEmployee(
        int $employeeId,
        int $limit = 20,
        bool $unreadOnly = false
    ) {
        $query = Notification::forEmployee($employeeId)
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->limit($limit)->get();
    }

    /**
     * الحصول على إشعارات مستخدم
     */
    public function getNotificationsForUser(
        int $userId,
        int $limit = 20,
        bool $unreadOnly = false
    ) {
        $query = Notification::forUser($userId)
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->limit($limit)->get();
    }

    /**
     * إرسال إشعارات الحجز
     */
    public function sendBookingNotification(
        string $bookingId,
        NotificationTypeEnum $type,
        string $customerName,
        string $serviceName,
        string $date,
        string $time,
        ?int $employeeId = null,
        ?int $userId = null,
        ?int $createdBy = null
    ): ?Notification {
        $title = $type->getTitle();
        $message = $this->generateBookingMessage($type, $customerName, $serviceName, $date, $time);

        $data = [
            'booking_id' => $bookingId,
            'customer_name' => $customerName,
            'service_name' => $serviceName,
            'date' => $date,
            'time' => $time,
        ];

        if ($employeeId) {
            return $this->sendToEmployee($employeeId, $type, $title, $message, $data, $createdBy);
        }

        if ($userId) {
            return $this->sendToUser($userId, $type, $title, $message, $data, $createdBy);
        }

        return null;
    }

    /**
     * إنشاء رسالة الحجز
     */
    private function generateBookingMessage(
        NotificationTypeEnum $type,
        string $customerName,
        string $serviceName,
        string $date,
        string $time
    ): string {
        return match ($type) {
            NotificationTypeEnum::BOOKING_CREATED => "تم إنشاء حجز جديد للعميل {$customerName} لخدمة {$serviceName} في {$date} الساعة {$time}",
            NotificationTypeEnum::BOOKING_CONFIRMED => "تم تأكيد حجزك لخدمة {$serviceName} في {$date} الساعة {$time}",
            NotificationTypeEnum::BOOKING_CANCELLED => "تم إلغاء حجزك لخدمة {$serviceName} في {$date} الساعة {$time}",
            NotificationTypeEnum::BOOKING_FINISHED => "تم إكمال خدمة {$serviceName} للعميل {$customerName} بنجاح",
            NotificationTypeEnum::BOOKING_REMINDER => "تذكير: لديك موعد لخدمة {$serviceName} مع العميل {$customerName} في {$date} الساعة {$time}",
            default => $type->getDefaultMessage()
        };
    }

    /**
     * إرسال إشعار تذكير بموعد
     */
    public function sendBookingReminder(
        int $employeeId,
        string $customerName,
        string $serviceName,
        string $date,
        string $time,
        int $minutesBefore = 30
    ): Notification {
        $title = "تذكير بموعد - {$minutesBefore} دقيقة";
        $message = "لديك موعد قريب مع العميل {$customerName} لخدمة {$serviceName} في {$time}";

        $data = [
            'customer_name' => $customerName,
            'service_name' => $serviceName,
            'date' => $date,
            'time' => $time,
            'minutes_before' => $minutesBefore,
            'reminder_type' => 'booking'
        ];

        return $this->sendToEmployee(
            $employeeId,
            NotificationTypeEnum::BOOKING_REMINDER,
            $title,
            $message,
            $data
        );
    }
}
