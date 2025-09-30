<?php

use App\Modules\Notification\Http\Controllers\Dashboard\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/dashboard')->group(function () {
  Route::middleware('baseAuthMiddleware:employee')->group(function () {
    // إشعارات الموظف الحالي
    Route::post('fetch_notifications', [NotificationController::class, 'fetchNotifications'])->name('fetch_notifications');
    Route::post('fetch_notifications_count', [NotificationController::class, 'fetchNotificationsCount'])->name('fetch_notifications_count');
    Route::post('mark_notification_read', [NotificationController::class, 'markNotificationRead'])->name('mark_notification_read');
    Route::post('mark_all_notifications_read', [NotificationController::class, 'markAllNotificationsRead'])->name('mark_all_notifications_read');
    Route::post('delete_notification', [NotificationController::class, 'deleteNotification'])->name('delete_notification');
    Route::post('delete_read_notifications', [NotificationController::class, 'deleteReadNotifications'])->name('delete_read_notifications');

    // إرسال إشعارات (للمديرين فقط)
    Route::middleware('admin')->group(function () {
      Route::post('notifications/send', [NotificationController::class, 'sendNotification'])->name('notifications.send');
      Route::get('notifications/types', [NotificationController::class, 'getNotificationTypes'])->name('notifications.types');
    });
  });
});
