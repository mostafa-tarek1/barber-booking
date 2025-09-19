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
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('slider_translations', function (Blueprint $table) {
            $table->id();
            $table->text('title')->nullable();
            $table->string('sub_title')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('slider_id')->constrained('sliders')->onDelete('cascade');
            $table->string('locale')->index();
            $table->unique(['slider_id', 'locale']);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slider_translations');
        Schema::dropIfExists('sliders');
    }
};
