<?php

use App\Modules\Notification\Http\Controllers\Api\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
  Route::middleware('baseAuthMiddleware:user')->group(function () {
    // إشعارات المستخدم الحالي
    Route::get('notifications', [NotificationController::class, 'getMyNotifications'])->name('api.notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('api.notifications.unread-count');
    Route::post('notifications/{notification}/mark-read', [NotificationController::class, 'markAsRead'])->name('api.notifications.mark-read');
    Route::post('notifications/{notification}/mark-unread', [NotificationController::class, 'markAsUnread'])->name('api.notifications.mark-unread');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
    Route::delete('notifications/{notification}', [NotificationController::class, 'deleteNotification'])->name('api.notifications.delete');
    Route::delete('notifications/read', [NotificationController::class, 'deleteReadNotifications'])->name('api.notifications.delete-read');
  });
});
