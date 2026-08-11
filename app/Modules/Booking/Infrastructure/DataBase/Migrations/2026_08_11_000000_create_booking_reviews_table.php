<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->unique()->constrained('bookings')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->string('status')->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_reviews');
    }
};
