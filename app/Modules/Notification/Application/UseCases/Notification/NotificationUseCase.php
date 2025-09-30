<?php

namespace App\Modules\Notification\Application\UseCases\Notification;

use App\Modules\Base\Application\Response\DataSuccess;
use App\Modules\Base\Application\Response\DataFailed;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Notification\Application\Services\NotificationService;
use App\Modules\Notification\Infrastructure\Persistence\Models\Notification\Notification;
use App\Modules\Notification\Domain\Enums\NotificationTypeEnum;

class NotificationUseCase
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * جلب إشعارات الموظف
     */
    public function getEmployeeNotifications(int $employeeId, int $limit = 20, bool $unreadOnly = false): DataStatus
    {
        try {
            $notifications = $this->notificationService->getNotificationsForEmployee(
                $employeeId,
                $limit,
                $unreadOnly
            );

            return new DataSuccess(
                $notifications->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'icon' => $notification->icon,
                        'color' => $notification->color,
                        'data' => $notification->data,
                        'is_read' => $notification->is_read,
                        'recipient_name' => $notification->recipient_name,
                        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                        'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
                    ];
                })->toArray(),
                null,
                [],
                true,
                200,
                'تم جلب الإشعارات بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في جلب الإشعارات'
            );
        }
    }

    /**
     * جلب إشعارات المستخدم
     */
    public function getUserNotifications(int $userId, int $limit = 20, bool $unreadOnly = false): DataStatus
    {
        try {
            $notifications = $this->notificationService->getNotificationsForUser(
                $userId,
                $limit,
                $unreadOnly
            );

            return new DataSuccess(
                $notifications->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'type' => $notification->type,
                        'icon' => $notification->icon,
                        'color' => $notification->color,
                        'data' => $notification->data,
                        'is_read' => $notification->is_read,
                        'recipient_name' => $notification->recipient_name,
                        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                        'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
                    ];
                })->toArray(),
                null,
                [],
                true,
                200,
                'تم جلب الإشعارات بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في جلب الإشعارات'
            );
        }
    }

    /**
     * جلب عدد الإشعارات غير المقروءة للموظف
     */
    public function getEmployeeUnreadCount(int $employeeId): DataStatus
    {
        try {
            $count = $this->notificationService->getUnreadCountForEmployee($employeeId);

            return new DataSuccess(
                ['unread_count' => $count],
                null,
                [],
                true,
                200,
                'تم جلب العدد بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في جلب العدد'
            );
        }
    }

    /**
     * جلب عدد الإشعارات غير المقروءة للمستخدم
     */
    public function getUserUnreadCount(int $userId): DataStatus
    {
        try {
            $count = $this->notificationService->getUnreadCountForUser($userId);

            return new DataSuccess(
                ['unread_count' => $count],
                null,
                [],
                true,
                200,
                'تم جلب العدد بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في جلب العدد'
            );
        }
    }

    /**
     * تحديد إشعار كمقروء
     */
    public function markAsRead(int $notificationId): DataStatus
    {
        try {
            $success = $this->notificationService->markAsRead($notificationId);

            if (!$success) {
                return new DataFailed(
                    ['notification_id' => 'الإشعار غير موجود'],
                    false,
                    404,
                    'الإشعار غير موجود'
                );
            }

            return new DataSuccess(
                null,
                null,
                [],
                true,
                200,
                'تم تحديد الإشعار كمقروء'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في تحديد الإشعار كمقروء'
            );
        }
    }

    /**
     * تحديد إشعار كغير مقروء
     */
    public function markAsUnread(int $notificationId): DataStatus
    {
        try {
            $success = $this->notificationService->markAsUnread($notificationId);

            if (!$success) {
                return new DataFailed(
                    ['notification_id' => 'الإشعار غير موجود'],
                    false,
                    404,
                    'الإشعار غير موجود'
                );
            }

            return new DataSuccess(
                null,
                null,
                [],
                true,
                200,
                'تم تحديد الإشعار كغير مقروء'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في تحديد الإشعار كغير مقروء'
            );
        }
    }

    /**
     * تحديد جميع إشعارات الموظف كمقروءة
     */
    public function markAllAsReadForEmployee(int $employeeId): DataStatus
    {
        try {
            $count = $this->notificationService->markAllAsReadForEmployee($employeeId);

            return new DataSuccess(
                ['marked_count' => $count],
                null,
                [],
                true,
                200,
                'تم تحديد جميع الإشعارات كمقروءة'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في تحديد الإشعارات كمقروءة'
            );
        }
    }

    /**
     * تحديد جميع إشعارات المستخدم كمقروءة
     */
    public function markAllAsReadForUser(int $userId): DataStatus
    {
        try {
            $count = $this->notificationService->markAllAsReadForUser($userId);

            return new DataSuccess(
                ['marked_count' => $count],
                null,
                [],
                true,
                200,
                'تم تحديد جميع الإشعارات كمقروءة'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في تحديد الإشعارات كمقروءة'
            );
        }
    }

    /**
     * حذف إشعار
     */
    public function deleteNotification(int $notificationId): DataStatus
    {
        try {
            $success = $this->notificationService->deleteNotification($notificationId);

            if (!$success) {
                return new DataFailed(
                    ['notification_id' => 'الإشعار غير موجود'],
                    false,
                    404,
                    'الإشعار غير موجود'
                );
            }

            return new DataSuccess(
                null,
                null,
                [],
                true,
                200,
                'تم حذف الإشعار بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في حذف الإشعار'
            );
        }
    }

    /**
     * حذف جميع الإشعارات المقروءة للموظف
     */
    public function deleteReadNotificationsForEmployee(int $employeeId): DataStatus
    {
        try {
            $count = $this->notificationService->deleteReadNotificationsForEmployee($employeeId);

            return new DataSuccess(
                ['deleted_count' => $count],
                null,
                [],
                true,
                200,
                'تم حذف الإشعارات المقروءة'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في حذف الإشعارات المقروءة'
            );
        }
    }

    /**
     * حذف جميع الإشعارات المقروءة للمستخدم
     */
    public function deleteReadNotificationsForUser(int $userId): DataStatus
    {
        try {
            $count = $this->notificationService->deleteReadNotificationsForUser($userId);

            return new DataSuccess(
                ['deleted_count' => $count],
                null,
                [],
                true,
                200,
                'تم حذف الإشعارات المقروءة'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في حذف الإشعارات المقروءة'
            );
        }
    }

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
    ): DataStatus {
        try {
            $notification = $this->notificationService->sendToEmployee(
                $employeeId,
                $type,
                $title,
                $message,
                $data,
                $createdBy
            );

            return new DataSuccess(
                [
                    'notification_id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                ],
                null,
                [],
                true,
                201,
                'تم إرسال الإشعار للموظف بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في إرسال الإشعار'
            );
        }
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
    ): DataStatus {
        try {
            $notification = $this->notificationService->sendToUser(
                $userId,
                $type,
                $title,
                $message,
                $data,
                $createdBy
            );

            return new DataSuccess(
                [
                    'notification_id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                ],
                null,
                [],
                true,
                201,
                'تم إرسال الإشعار للمستخدم بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في إرسال الإشعار'
            );
        }
    }

    /**
     * إرسال إشعار الحجز
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
    ): DataStatus {
        try {
            $notification = $this->notificationService->sendBookingNotification(
                $bookingId,
                $type,
                $customerName,
                $serviceName,
                $date,
                $time,
                $employeeId,
                $userId,
                $createdBy
            );

            if (!$notification) {
                return new DataFailed(
                    ['error' => 'لم يتم تحديد مستلم للإشعار'],
                    false,
                    400,
                    'لم يتم تحديد مستلم للإشعار'
                );
            }

            return new DataSuccess(
                [
                    'notification_id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                ],
                null,
                [],
                true,
                201,
                'تم إرسال إشعار الحجز بنجاح'
            );
        } catch (\Exception $e) {
            return new DataFailed(
                ['error' => $e->getMessage()],
                false,
                500,
                'حدث خطأ في إرسال إشعار الحجز'
            );
        }
    }
}
