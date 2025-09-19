<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('booking_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_additional_id')->nullable()
                ->constrained('room_additionals')
                ->onDelete('set null');
            $table->unsignedBigInteger('status')->default(0)->comment('0 => pending, 1 => confirmed, 2 => cancelled')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('booking_rooms');
    }
};
