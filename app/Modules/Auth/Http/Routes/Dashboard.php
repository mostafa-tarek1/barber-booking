<?php

use App\Modules\Auth\Http\Controllers\Dashboard\ActivityLog\ActivityLogController;
use App\Modules\Auth\Http\Controllers\Dashboard\Attendance\AttendanceController;
use App\Modules\Auth\Http\Controllers\Dashboard\Customer\UserController;
use App\Modules\Auth\Http\Controllers\Dashboard\Employee\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->group(function () {
    Route::post('login', [EmployeeController::class, 'login']);
});

// Employee Routes

Route::prefix('dashboard')->middleware('baseAuthMiddleware:employee')->group(function () {
    Route::controller(EmployeeController::class)->group(function () {
        Route::post('logout', 'logout')->name('logout');
        Route::post('fetch_employees', 'fetchEmployees')->name('fetch_employees');
        Route::post('fetch_employee_details', 'fetchEmployeeDetails')->name('fetch_employee_details');
        Route::post('create_employee', 'createEmployee')->name('create_employee');
        Route::post('update_employee', 'updateEmployee')->name('update_employee');
        Route::post('delete_employee', 'deleteEmployee')->name('delete_employee');
    });
    Route::controller(AttendanceController::class)->group(function () {
        Route::post('attendance_status', 'status');
        Route::post('attendance_punch', 'punch');
        Route::post('fetch_attendance_settings', 'settings');
        Route::post('update_attendance_settings', 'updateSettings');
        Route::post('fetch_attendance_report', 'report');
    });
    Route::controller(UserController::class)->group(function () {
        Route::post('create_user', 'createUser');
        Route::post('delete_user', 'deleteUser');
        Route::post('fetch_users', 'fetchUsers');
        Route::post('fetch_user_details', 'fetchUserDetails');
        Route::post('update_user', 'updateUser');
        Route::post('force_logout', 'forceLogout');
        Route::post('block_user', 'blockUser');
        Route::post('unblock_user', 'unblockUser');
        Route::post('toggle_approval', 'toggleUserApproval');

        // fetch permissions
        Route::post('updateOrCreatePermission', [\App\Modules\Auth\Http\Controllers\Dashboard\Permission\PermissionController::class, 'store_permission'])->name('create_permissions');

    });
    // user_id

    Route::post('activity_logs', [ActivityLogController::class, 'index'])
        ->middleware('Super_Admin_Only');
});
