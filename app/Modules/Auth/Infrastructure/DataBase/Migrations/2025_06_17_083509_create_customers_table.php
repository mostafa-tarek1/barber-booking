<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_blocked')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_phone_verified')->default(false);
            $table->timestamp('phone_verified_at')->nullable();
            $table->boolean('is_email_verified')->default(false);
            $table->boolean('is_online')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->string('name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->nullable()->unique();
            $table->tinyInteger('client_type')->default(1); // 1 for customer, 2 for vendor
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone_code')->nullable();
            $table->string('country_code')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            // $table->foreign('location_id')->references('id')->on('locations')->onDelete('set null');
            $table->unsignedBigInteger('nationality_id')->nullable();
            // $table->foreign('nationality_id')->references('id')->on('nationalities')->onDelete('set null');
            $table->string('identify_number')->nullable()->unique();
            $table->string('verification_code')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('image')->nullable();
            $table->string('cover')->nullable();
            $table->text('commercial_register')->nullable();
            $table->text('tax_register')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->text('fcm_token')->nullable();
            $table->text('password')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->unsignedTinyInteger('platform')->default(1)->comment('1 mobile, 2 dashboard');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->onDelete('set null');
            // $table->constrained('users')->nullOnDelete();
            // $table->foreignId('user_id')->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->boolean('is_master')->default(false);
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->text('address');
            $table->text('file_address')->nullable();
            $table->timestamps();

        });

        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('device_id')->nullable();
            $table->string('device_name')->nullable();
            $table->string('os_version')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_token')->nullable();
            $table->string('device_brand')->nullable();
            $table->string('device_model')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
