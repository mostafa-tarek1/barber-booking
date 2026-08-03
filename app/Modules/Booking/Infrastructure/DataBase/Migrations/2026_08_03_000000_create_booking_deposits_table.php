<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('collected_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('payment_method', ['cash', 'card', 'wallet']);
            $table->decimal('amount', 10, 2);
            $table->timestamp('collected_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_deposits');
    }
};
