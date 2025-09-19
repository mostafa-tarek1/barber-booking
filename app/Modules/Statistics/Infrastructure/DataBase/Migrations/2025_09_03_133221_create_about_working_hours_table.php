<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('about_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_id')
                ->constrained('about')
                ->onDelete('cascade');

            $table->json('days')->nullable();
            $table->time('from_time')->nullable();
            $table->time('to_time')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_working_hours');
    }
};
