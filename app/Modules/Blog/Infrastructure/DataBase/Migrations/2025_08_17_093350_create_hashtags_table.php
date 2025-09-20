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
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->string('slug')->nullable()->unique();
            $table->timestamps();
            $table->foreign('created_by')->references('id')->on('employees')->onDelete('cascade');
        });


        Schema::create('hashtag_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hashtag_id');
            $table->string('locale')->index(); // en, ar,
            $table->string('title');
            $table->unique(['hashtag_id', 'locale']);
            $table->foreign('hashtag_id')->references('id')->on('hashtags')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hashtags');
        Schema::dropIfExists('hashtags_translations');
    }
};
