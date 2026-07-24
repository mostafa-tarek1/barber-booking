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
      // فهارس إضافية لتحسين أداء الإحصائيات
      $table->index(['status', 'created_at', 'id'], 'idx_bookings_status_created_id');
      $table->index(['date', 'status'], 'idx_bookings_date_status_perf');
    });

    Schema::table('booking_service', function (Blueprint $table) {
      // فهارس محسنة للجداول الوسيطة
      $table->index(['employee_id', 'service_id', 'created_at'], 'idx_booking_service_employee_service_created');
      $table->index(['service_id', 'created_at'], 'idx_booking_service_service_created');
      $table->index(['booking_id', 'service_id', 'employee_id'], 'idx_booking_service_composite');
    });

    Schema::table('employees', function (Blueprint $table) {
      // فهارس محسنة للموظفين
      $table->index(['employee_type', 'status', 'id'], 'idx_employees_type_status_id');
    });

    Schema::table('services', function (Blueprint $table) {
      // فهارس محسنة للخدمات
      $table->index(['is_active', 'id'], 'idx_services_active_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('bookings', function (Blueprint $table) {
      $table->dropIndex('idx_bookings_status_created_id');
      $table->dropIndex('idx_bookings_date_status_perf');
    });

    Schema::table('booking_service', function (Blueprint $table) {
      $table->dropIndex('idx_booking_service_employee_service_created');
      $table->dropIndex('idx_booking_service_service_created');
      $table->dropIndex('idx_booking_service_composite');
    });

    Schema::table('employees', function (Blueprint $table) {
      $table->dropIndex('idx_employees_type_status_id');
    });

    Schema::table('services', function (Blueprint $table) {
      $table->dropIndex('idx_services_active_id');
    });
  }
};
