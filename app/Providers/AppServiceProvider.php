<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('public-booking-create', function (Request $request): array {
            $phone = preg_replace('/\D+/', '', (string) $request->input('phone'));

            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perHour(3)->by($request->ip().'|'.$phone),
            ];
        });

        RateLimiter::for('public-booking-read', function (Request $request): Limit {
            return Limit::perMinute(60)->by($request->ip());
        });

        $featuresPath = app_path('Modules');
        $features = File::directories($featuresPath);

        foreach ($features as $feature) {
            $featureName = basename($feature);
            $providerClass = "App\\Modules\\{$featureName}\\Http\\Providers\\{$featureName}ServiceProvider";

            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }
}
