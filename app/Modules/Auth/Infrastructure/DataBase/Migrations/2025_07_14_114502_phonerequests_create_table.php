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
        Schema::create('phone_requests', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->nullable();
            $table->string('credential')->nullable();
            $table->string('phone_code')->nullable();
            $table->boolean('is_verified')->default(0);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_requests');
    }
};