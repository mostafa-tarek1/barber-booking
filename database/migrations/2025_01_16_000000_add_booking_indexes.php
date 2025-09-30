<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // إضافة فهارس لتحسين الأداء
            $table->index(['date', 'status'], 'idx_bookings_date_status');
            $table->index(['status', 'created_at'], 'idx_bookings_status_created');
            $table->index(['created_by', 'status'], 'idx_bookings_created_by_status');
            $table->index(['date', 'from_hour'], 'idx_bookings_date_time');
            $table->index(['phone'], 'idx_bookings_phone');
            $table->index(['email'], 'idx_bookings_email');
        });

        Schema::table('booking_service', function (Blueprint $table) {
            // فهارس للجدول الوسيط
            $table->index(['booking_id', 'employee_id'], 'idx_booking_service_booking_employee');
            $table->index(['employee_id', 'created_at'], 'idx_booking_service_employee_created');
            $table->index(['service_id'], 'idx_booking_service_service');
        });

        Schema::table('services', function (Blueprint $table) {
            // فهارس للخدمات
            $table->index(['is_active', 'price'], 'idx_services_active_price');
            $table->index(['duration'], 'idx_services_duration');
        });

        Schema::table('employees', function (Blueprint $table) {
            // فهارس للموظفين
            $table->index(['status', 'employee_type'], 'idx_employees_status_type');
            $table->index(['employee_type'], 'idx_employees_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_date_status');
            $table->dropIndex('idx_bookings_status_created');
            $table->dropIndex('idx_bookings_created_by_status');
            $table->dropIndex('idx_bookings_date_time');
            $table->dropIndex('idx_bookings_phone');
            $table->dropIndex('idx_bookings_email');
        });

        Schema::table('booking_service', function (Blueprint $table) {
            $table->dropIndex('idx_booking_service_booking_employee');
            $table->dropIndex('idx_booking_service_employee_created');
            $table->dropIndex('idx_booking_service_service');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('idx_services_active_price');
            $table->dropIndex('idx_services_duration');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_employees_status_type');
            $table->dropIndex('idx_employees_type');
        });
    }
};
