<?php

namespace App\Modules\Notification\Http\Controllers\Dashboard\Notification;

use App\Http\Controllers\Controller;
use App\Modules\Notification\Application\Services\NotificationService;
use App\Modules\Notification\Application\UseCases\Notification\NotificationUseCase;
use App\Modules\Base\Domain\Holders\AuthHolder;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;
    protected NotificationUseCase $notificationUseCase;

    public function __construct(
        NotificationService $notificationService,
        NotificationUseCase $notificationUseCase
    ) {
        $this->notificationService = $notificationService;
        $this->notificationUseCase = $notificationUseCase;
    }

    /**
     * جلب إشعارات الموظف الحالي
     */
    public function fetchNotifications(Request $request)
    {
        $employee = AuthHolder::getInstance()->getUser();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح بالوصول'
            ], 401);
        }

        $limit = $request->get('limit', 20);
        $unreadOnly = $request->boolean('unread_only', false);

        $notifications = $this->notificationService->getNotificationsForEmployee(
            $employee->id,
            $limit,
            $unreadOnly
        );

        return response()->json([
            'status' => true,
            'message' => 'تم جلب الإشعارات بنجاح',
            'data' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'icon' => $notification->icon,
                    'color' => $notification->color,
                    'data' => $notification->data,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
                ];
            })
        ]);
    }

    /**
     * جلب عدد الإشعارات غير المقروءة
     */
    public function fetchNotificationsCount()
    {
        $employee = AuthHolder::getInstance()->getUser();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح بالوصول'
            ], 401);
        }

        $count = $this->notificationService->getUnreadCountForEmployee($employee->id);

        return response()->json([
            'status' => true,
            'message' => 'تم جلب العدد بنجاح',
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * تحديد إشعار كمقروء
     */
    public function markNotificationRead(Request $request)
    {
        $employee = AuthHolder::getInstance()->getUser();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح بالوصول'
            ], 401);
        }

        $request->validate([
            'notification_id' => 'required|integer|exists:notifications,id'
        ]);

        $notification = \App\Modules\Notification\Infrastructure\Persistence\Models\Notification\Notification::find($request->notification_id);

        // التحقق من أن الإشعار مخصص لهذا الموظف
        if (!$notification || $notification->employee_id !== $employee->id) {
            return response()->json([
                'status' => false,
                'message' => 'الإشعار غير موجود أو غير مخصص لك'
            ], 404);
        }

        $this->notificationService->markAsRead($request->notification_id);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديد الإشعار كمقروء'
        ]);
    }


    /**
     * تحديد جميع الإشعارات كمقروءة
     */
    public function markAllNotificationsRead()
    {
        $employee = AuthHolder::getInstance()->getUser();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح بالوصول'
            ], 401);
        }

        $count = $this->notificationService->markAllAsReadForEmployee($employee->id);

        return response()->json([
            'status' => true,
            'message' => 'تم تحديد جميع الإشعارات كمقروءة',
            'data' => [
                'marked_count' => $count
            ]
        ]);
    }

    /**
     * حذف إشعار
     */
    public function deleteNotification(Request $request)
    {
        $employee = AuthHolder::getInstance()->getUser();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح بالوصول'
            ], 401);
        }

        $request->validate([
            'notification_id' => 'required|integer|exists:notifications,id'
        ]);

        $notification = \App\Modules\Notification\Infrastructure\Persistence\Models\Notification\Notification::find($request->notification_id);

        // التحقق من أن الإشعار مخصص لهذا الموظف
        if (!$notification || $notification->employee_id !== $employee->id) {
            return response()->json([
                'status' => false,
                'message' => 'الإشعار غير موجود أو غير مخصص لك'
            ], 404);
        }

        $this->notificationService->deleteNotification($request->notification_id);

        return response()->json([
            'status' => true,
            'message' => 'تم حذف الإشعار بنجاح'
        ]);
    }

    /**
     * حذف جميع الإشعارات المقروءة
     */
    public function deleteReadNotifications()
    {
        $employee = AuthHolder::getInstance()->getUser();

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح بالوصول'
            ], 401);
        }

        $count = $this->notificationService->deleteReadNotificationsForEmployee($employee->id);

        return response()->json([
            'status' => true,
            'message' => 'تم حذف الإشعارات المقروءة',
            'data' => [
                'deleted_count' => $count
            ]
        ]);
    }

    /**
     * إرسال إشعار جديد (للمديرين فقط)
     */
    public function sendNotification(Request $request)
    {
        $request->validate([
            'recipient_type' => 'required|in:employee,user,all_employees,all_users,broadcast',
            'recipient_id' => 'required_if:recipient_type,employee,user|integer',
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array'
        ]);

        $employee = AuthHolder::getInstance()->getUser();

        if (!$employee || !in_array($employee->employee_type, ['admin', 'super_admin'])) {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح بالوصول - مطلوب صلاحية مدير'
            ], 403);
        }

        try {
            $type = \App\Modules\Notification\Domain\Enums\NotificationTypeEnum::from($request->type);

            $result = match ($request->recipient_type) {
                'employee' => $this->notificationService->sendToEmployee(
                    $request->recipient_id,
                    $type,
                    $request->title,
                    $request->message,
                    $request->data ?? [],
                    $employee->id
                ),
                'user' => $this->notificationService->sendToUser(
                    $request->recipient_id,
                    $type,
                    $request->title,
                    $request->message,
                    $request->data ?? [],
                    $employee->id
                ),
                'all_employees' => $this->notificationService->sendToAllEmployees(
                    $type,
                    $request->title,
                    $request->message,
                    $request->data ?? [],
                    $employee->id
                ),
                'all_users' => $this->notificationService->sendToAllUsers(
                    $type,
                    $request->title,
                    $request->message,
                    $request->data ?? [],
                    $employee->id
                ),
                'broadcast' => $this->notificationService->sendBroadcast(
                    $type,
                    $request->title,
                    $request->message,
                    $request->data ?? [],
                    $employee->id
                ),
            };

            $message = match ($request->recipient_type) {
                'employee' => 'تم إرسال الإشعار للموظف بنجاح',
                'user' => 'تم إرسال الإشعار للمستخدم بنجاح',
                'all_employees' => 'تم إرسال الإشعار لجميع الموظفين بنجاح',
                'all_users' => 'تم إرسال الإشعار لجميع المستخدمين بنجاح',
                'broadcast' => 'تم إرسال الإشعار للجميع بنجاح',
            };

            return response()->json([
                'status' => true,
                'message' => $message,
                'data' => [
                    'sent_count' => is_array($result) ? count($result) : 1
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ في إرسال الإشعار: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب أنواع الإشعارات المتاحة
     */
    public function getNotificationTypes()
    {
        $types = \App\Modules\Notification\Domain\Enums\NotificationTypeEnum::cases();

        $formattedTypes = array_map(function ($type) {
            return [
                'value' => $type->value,
                'title' => $type->getTitle(),
                'icon' => $type->getIcon(),
                'color' => $type->getColor(),
                'priority' => $type->getPriority(),
                'is_urgent' => $type->isUrgent()
            ];
        }, $types);

        return response()->json([
            'status' => true,
            'message' => 'تم جلب أنواع الإشعارات بنجاح',
            'data' => $formattedTypes
        ]);
    }
}
