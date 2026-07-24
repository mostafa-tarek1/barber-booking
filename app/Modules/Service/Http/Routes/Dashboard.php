<?php

use App\Modules\Service\Http\Controllers\Dashboard\Service\ServiceController;
use App\Modules\Service\Http\Controllers\Dashboard\Package\PackageController;
use Illuminate\Support\Facades\Route;


Route::prefix('dashboard')->group(function () {
    Route::middleware('baseAuthMiddleware:employee')->group(function () {
        Route::post('fetch_services', [ServiceController::class, 'fetchServices']);
        Route::post('fetch_service_details', [ServiceController::class, 'fetchServiceDetails']);
        Route::post('fetch_packages', [PackageController::class, 'index']);
        Route::middleware('employeeRole:admin,super_admin')->group(function () {
            Route::post('create_service', [ServiceController::class, 'createService']);
            Route::post('update_service', [ServiceController::class, 'updateService']);
            Route::post('delete_service', [ServiceController::class, 'deleteService']);
            Route::post('create_package', [PackageController::class, 'store']);
            Route::post('update_package', [PackageController::class, 'update']);
            Route::post('delete_package', [PackageController::class, 'destroy']);
        });
    });
});
