<?php

use App\Modules\Service\Http\Controllers\Api\Service\ServiceController;
use App\Modules\Service\Http\Controllers\Api\Package\PackageController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::post('fetch_services', [ServiceController::class, 'fetchServices']);
    Route::post('fetch_packages', [PackageController::class, 'index']);
});
