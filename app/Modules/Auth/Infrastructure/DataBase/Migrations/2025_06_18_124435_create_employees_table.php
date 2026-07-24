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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_blocked')->default(false);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->text('password')->nullable();
            $table->enum('employee_type', ['admin', 'super_admin', 'employee', 'barber'])->default('admin');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('employees')->onDelete('set null');
            $table->string('image')->nullable();
            $table->string('cover')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('country_code')->nullable();
            $table->string('identify_number')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->text('fcm_token')->nullable();
            $table->string('username')->nullable();
            $table->string('avatar')->nullable();
            $table->string('id_type')->nullable();
            $table->string('id_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_email_verified')->default(false);
            $table->boolean('is_phone_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->string('last_os')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
