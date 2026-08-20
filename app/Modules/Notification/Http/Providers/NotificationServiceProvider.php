<?php

namespace App\Modules\Notification\Http\Providers;

use App\Modules\Notification\Application\Events\BookingCancelled;
use App\Modules\Notification\Application\Events\BookingConfirmed;
use App\Modules\Notification\Application\Events\BookingCreated;
use App\Modules\Notification\Application\Events\BookingFinished;
use App\Modules\Notification\Application\Listeners\SendBookingConfirmedNotification;
use App\Modules\Notification\Application\Listeners\SendBookingConfirmedWhatsAppNotification;
use App\Modules\Notification\Application\Listeners\SendBookingCreatedNotification;
use App\Modules\Notification\Application\Listeners\SendBookingCreatedWhatsAppNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // تحميل الـ migrations
        $this->loadMigrationsFrom(app_path('Modules/Notification/Infrastructure/DataBase/Migrations'));

        // تحميل الـ routes
        $this->loadRoutesFrom(app_path('Modules/Notification/Http/Routes/Api.php'));
        $this->loadRoutesFrom(app_path('Modules/Notification/Http/Routes/Dashboard.php'));

        // تسجيل Event Listeners
        Event::listen(
            BookingCreated::class,
            SendBookingCreatedNotification::class
        );

        Event::listen(
            BookingCreated::class,
            SendBookingCreatedWhatsAppNotification::class
        );

        Event::listen(
            BookingConfirmed::class,
            SendBookingConfirmedNotification::class
        );

        Event::listen(
            BookingConfirmed::class,
            SendBookingConfirmedWhatsAppNotification::class
        );

        // يمكن إضافة المزيد من الـ listeners هنا
        // Event::listen(
        //     BookingCancelled::class,
        //     SendBookingCancelledNotification::class
        // );

        // Event::listen(
        //     BookingFinished::class,
        //     SendBookingFinishedNotification::class
        // );
    }
}
