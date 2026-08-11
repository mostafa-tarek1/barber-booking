<?php

use App\Modules\Finance\Http\Controllers\Dashboard\Expense\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')
    ->middleware(['baseAuthMiddleware:employee', 'employeeRole:admin,super_admin'])
    ->controller(ExpenseController::class)
    ->group(function () {
        Route::post('fetch_expenses', 'index');
        Route::post('create_expense', 'store');
        Route::post('update_expense', 'update');
        Route::post('delete_expense', 'destroy');
        Route::post('fetch_financial_report', 'report');
    });
