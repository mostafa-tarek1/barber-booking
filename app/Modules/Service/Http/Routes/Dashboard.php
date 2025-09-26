<?php

use App\Modules\Service\Http\Controllers\Dashboard\Service\ServiceController;
use Illuminate\Support\Facades\Route;


Route::prefix('dashboard')->group(function () {
    Route::middleware('baseAuthMiddleware:employee')->group(function () {
        Route::post('fetch_services', [ServiceController::class, 'fetchServices']);
        Route::post('fetch_service_details', [ServiceController::class, 'fetchServiceDetails']);
        Route::post('create_service', [ServiceController::class, 'createService']);
        Route::post('update_service', [ServiceController::class, 'updateService']);
        Route::post('delete_service', [ServiceController::class, 'deleteService']);
    });
});
