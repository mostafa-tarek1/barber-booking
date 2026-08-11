<?php

use App\Modules\Finance\Http\Controllers\Dashboard\Expense\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')
    ->middleware('baseAuthMiddleware:employee')
    ->controller(ExpenseController::class)
    ->group(function () {
        Route::middleware('employeeRole:admin,super_admin,employee')->group(function () {
            Route::post('fetch_expenses', 'index');
            Route::post('create_expense', 'store');
            Route::post('fetch_financial_report', 'report');
        });

        Route::middleware('employeeRole:admin,super_admin')->group(function () {
            Route::post('update_expense', 'update');
            Route::post('delete_expense', 'destroy');
        });
    });
