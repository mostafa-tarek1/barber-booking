<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Base\Http\Providers\BaseServiceProvider::class,
    App\Modules\Auth\Http\Providers\AuthServiceProvider::class,
    App\Modules\Notification\Http\Providers\NotificationServiceProvider::class,
];
