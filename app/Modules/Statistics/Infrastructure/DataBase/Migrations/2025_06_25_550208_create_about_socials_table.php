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
        Schema::create('about_socials', function (Blueprint $table) {
            $table->id();
            $table->boolean("is_active")->default(true);
            $table->string("key")->nullable();
            $table->string("value")->nullable();
            $table->text("image")->nullable();
            $table->foreignId('about_id')->nullable()->constrained('about')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_socials');
    }
};
