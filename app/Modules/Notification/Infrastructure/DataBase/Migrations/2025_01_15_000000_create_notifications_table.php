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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('type', [
                'booking_created',
                'booking_confirmed',
                'booking_cancelled',
                'booking_finished',
                'booking_reminder',
                'service_added',
                'service_updated',
                'employee_added',
                'system_alert',
                'payment_received',
                'review_received'
            ]);
            $table->json('data')->nullable(); // بيانات إضافية للإشعار
            $table->timestamp('read_at')->nullable();

            // المستلم - إما مستخدم أو موظف
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('employee_id')->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');

            // من أنشأ/عدل الإشعار
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('employees')->onDelete('set null');

            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('employees')->onDelete('set null');

            $table->timestamps();

            // فهارس للأداء
            $table->index(['user_id', 'read_at']);
            $table->index(['employee_id', 'read_at']);
            $table->index(['type', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
