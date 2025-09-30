<?php

namespace App\Modules\Notification\Domain\Enums;

enum NotificationTypeEnum: string
{
    case BOOKING_CREATED = 'booking_created';
    case BOOKING_CONFIRMED = 'booking_confirmed';
    case BOOKING_CANCELLED = 'booking_cancelled';
    case BOOKING_FINISHED = 'booking_finished';
    case BOOKING_REMINDER = 'booking_reminder';
    case SERVICE_ADDED = 'service_added';
    case SERVICE_UPDATED = 'service_updated';
    case EMPLOYEE_ADDED = 'employee_added';
    case SYSTEM_ALERT = 'system_alert';
    case PAYMENT_RECEIVED = 'payment_received';
    case REVIEW_RECEIVED = 'review_received';

    /**
     * الحصول على عنوان الإشعار بالعربية
     */
    public function getTitle(): string
    {
        return match ($this) {
            self::BOOKING_CREATED => 'حجز جديد',
            self::BOOKING_CONFIRMED => 'تم تأكيد الحجز',
            self::BOOKING_CANCELLED => 'تم إلغاء الحجز',
            self::BOOKING_FINISHED => 'تم إكمال الخدمة',
            self::BOOKING_REMINDER => 'تذكير بموعد الحجز',
            self::SERVICE_ADDED => 'خدمة جديدة',
            self::SERVICE_UPDATED => 'تم تحديث الخدمة',
            self::EMPLOYEE_ADDED => 'موظف جديد',
            self::SYSTEM_ALERT => 'تنبيه نظام',
            self::PAYMENT_RECEIVED => 'تم استلام الدفع',
            self::REVIEW_RECEIVED => 'تقييم جديد',
        };
    }

    /**
     * الحصول على رسالة الإشعار الافتراضية
     */
    public function getDefaultMessage(): string
    {
        return match ($this) {
            self::BOOKING_CREATED => 'تم إنشاء حجز جديد',
            self::BOOKING_CONFIRMED => 'تم تأكيد حجزك بنجاح',
            self::BOOKING_CANCELLED => 'تم إلغاء حجزك',
            self::BOOKING_FINISHED => 'تم إكمال الخدمة بنجاح',
            self::BOOKING_REMINDER => 'تذكير: لديك موعد قريب',
            self::SERVICE_ADDED => 'تمت إضافة خدمة جديدة',
            self::SERVICE_UPDATED => 'تم تحديث إحدى الخدمات',
            self::EMPLOYEE_ADDED => 'تمت إضافة موظف جديد',
            self::SYSTEM_ALERT => 'تنبيه مهم من النظام',
            self::PAYMENT_RECEIVED => 'تم استلام الدفع بنجاح',
            self::REVIEW_RECEIVED => 'تم استلام تقييم جديد',
        };
    }

    /**
     * الحصول على أيقونة الإشعار
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::BOOKING_CREATED => 'calendar-plus',
            self::BOOKING_CONFIRMED => 'calendar-check',
            self::BOOKING_CANCELLED => 'calendar-x',
            self::BOOKING_FINISHED => 'calendar-check-2',
            self::BOOKING_REMINDER => 'bell',
            self::SERVICE_ADDED => 'plus-circle',
            self::SERVICE_UPDATED => 'edit-circle',
            self::EMPLOYEE_ADDED => 'user-plus',
            self::SYSTEM_ALERT => 'alert-triangle',
            self::PAYMENT_RECEIVED => 'credit-card',
            self::REVIEW_RECEIVED => 'star',
        };
    }

    /**
     * الحصول على لون الإشعار
     */
    public function getColor(): string
    {
        return match ($this) {
            self::BOOKING_CREATED => 'blue',
            self::BOOKING_CONFIRMED => 'green',
            self::BOOKING_CANCELLED => 'red',
            self::BOOKING_FINISHED => 'purple',
            self::BOOKING_REMINDER => 'yellow',
            self::SERVICE_ADDED => 'green',
            self::SERVICE_UPDATED => 'blue',
            self::EMPLOYEE_ADDED => 'green',
            self::SYSTEM_ALERT => 'red',
            self::PAYMENT_RECEIVED => 'green',
            self::REVIEW_RECEIVED => 'yellow',
        };
    }

    /**
     * الحصول على أولوية الإشعار (1-5)
     */
    public function getPriority(): int
    {
        return match ($this) {
            self::SYSTEM_ALERT => 5,
            self::BOOKING_CANCELLED => 4,
            self::BOOKING_REMINDER => 3,
            self::BOOKING_CONFIRMED, self::BOOKING_FINISHED => 2,
            default => 1,
        };
    }

    /**
     * تحديد ما إذا كان الإشعار عاجل
     */
    public function isUrgent(): bool
    {
        return $this->getPriority() >= 4;
    }

    /**
     * الحصول على جميع أنواع الإشعارات
     */
    public static function getAllTypes(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

    /**
     * الحصول على أنواع الإشعارات العاجلة
     */
    public static function getUrgentTypes(): array
    {
        return array_filter(
            self::cases(),
            fn($case) => $case->isUrgent()
        );
    }
}
