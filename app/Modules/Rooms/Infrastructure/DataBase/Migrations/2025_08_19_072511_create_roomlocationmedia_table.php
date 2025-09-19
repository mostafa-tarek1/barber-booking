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
        Schema::create('room_location_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('room_location_id')
                ->constrained('room_locations')
                ->cascadeOnDelete();

            $table->tinyInteger('type')->comment('1=slider, 2=gallery, 3=reel');

            $table->string('file');

            $table->string('alt')->nullable();
            $table->string('title')->nullable();

            $table->tinyInteger('file_type')->comment('1=image, 2=video, 3=audio');

            $table->boolean('status')->default(true)->index();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_location_media');
    }
};
