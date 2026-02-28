<?php

namespace App\Modules\Notification\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use App\Modules\Notification\Application\Services\NotificationService;
use App\Modules\Base\Domain\Holders\AuthHolder;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * جلب إشعارات المستخدم الحالي
     */
    public function getMyNotifications(Request $request)
    {
        $user = AuthHolder::getInstance()->getUser();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'غير مصرح بالوصول',
                'data' => null,
            ], 401);
        }

        $limit = $request->get('limit', 20);
        $unreadOnly = $request->boolean('unread_only', false);

        $notifications = $this->notificationService->getNotificationsForUser(
            $user->id,
            $limit,
            $unreadOnly
        );

        return response()->json([
            'status' => 200,
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
    public function getUnreadCount()
    {
        $user = AuthHolder::getInstance()->getUser();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'غير مصرح بالوصول',
                'data' => null,
            ], 401);
        }

        $count = $this->notificationService->getUnreadCountForUser($user->id);

        return response()->json([
            'status' => 200,
            'message' => 'تم جلب العدد بنجاح',
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    /**
     * تحديد إشعار كمقروء
     */
    public function markAsRead(Request $request, int $notificationId)
    {
        $user = AuthHolder::getInstance()->getUser();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'غير مصرح بالوصول',
                'data' => null,
            ], 401);
        }

        $notification = \App\Modules\Notification\Infrastructure\Persistence\Models\Notification\Notification::find($notificationId);

        // التحقق من أن الإشعار مخصص لهذا المستخدم
        if (!$notification || $notification->user_id !== $user->id) {
            return response()->json([
                'status' => 404,
                'message' => 'الإشعار غير موجود أو غير مخصص لك',
                'data' => null,
            ], 404);
        }

        $this->notificationService->markAsRead($notificationId);

        return response()->json([
            'status' => 200,
            'message' => 'تم تحديد الإشعار كمقروء',
            'data' => null,
        ]);
    }

    /**
     * تحديد إشعار كغير مقروء
     */
    public function markAsUnread(Request $request, int $notificationId)
    {
        $user = AuthHolder::getInstance()->getUser();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'غير مصرح بالوصول',
                'data' => null,
            ], 401);
        }

        $notification = \App\Modules\Notification\Infrastructure\Persistence\Models\Notification\Notification::find($notificationId);

        // التحقق من أن الإشعار مخصص لهذا المستخدم
        if (!$notification || $notification->user_id !== $user->id) {
            return response()->json([
                'status' => 404,
                'message' => 'الإشعار غير موجود أو غير مخصص لك',
                'data' => null,
            ], 404);
        }

        $this->notificationService->markAsUnread($notificationId);

        return response()->json([
            'status' => 200,
            'message' => 'تم تحديد الإشعار كغير مقروء',
            'data' => null,
        ]);
    }

    /**
     * تحديد جميع الإشعارات كمقروءة
     */
    public function markAllAsRead()
    {
        $user = AuthHolder::getInstance()->getUser();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'غير مصرح بالوصول',
                'data' => null,
            ], 401);
        }

        $count = $this->notificationService->markAllAsReadForUser($user->id);

        return response()->json([
            'status' => 200,
            'message' => 'تم تحديد جميع الإشعارات كمقروءة',
            'data' => [
                'marked_count' => $count
            ]
        ]);
    }

    /**
     * حذف إشعار
     */
    public function deleteNotification(Request $request, int $notificationId)
    {
        $user = AuthHolder::getInstance()->getUser();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'غير مصرح بالوصول',
                'data' => null,
            ], 401);
        }

        $notification = \App\Modules\Notification\Infrastructure\Persistence\Models\Notification\Notification::find($notificationId);

        // التحقق من أن الإشعار مخصص لهذا المستخدم
        if (!$notification || $notification->user_id !== $user->id) {
            return response()->json([
                'status' => 404,
                'message' => 'الإشعار غير موجود أو غير مخصص لك',
                'data' => null,
            ], 404);
        }

        $this->notificationService->deleteNotification($notificationId);

        return response()->json([
            'status' => 200,
            'message' => 'تم حذف الإشعار بنجاح',
            'data' => null,
        ]);
    }

    /**
     * حذف جميع الإشعارات المقروءة
     */
    public function deleteReadNotifications()
    {
        $user = AuthHolder::getInstance()->getUser();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'غير مصرح بالوصول',
                'data' => null,
            ], 401);
        }

        $count = $this->notificationService->deleteReadNotificationsForUser($user->id);

        return response()->json([
            'status' => 200,
            'message' => 'تم حذف الإشعارات المقروءة',
            'data' => [
                'deleted_count' => $count
            ]
        ]);
    }
}
