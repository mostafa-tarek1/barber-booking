<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('about_us', function (Blueprint $table) {
            $table->id();
            $table->string('main_image')->nullable();
            $table->timestamps();
        });
        Schema::create('about_us_translations', function (Blueprint $table) {
            $table->id();
            $table->text('title')->nullable();
            $table->string('sub_title')->nullable();
            $table->text("description")->nullable();
            $table->text('our_mission')->nullable();
            $table->text('our_vision')->nullable();
            $table->text('our_goal')->nullable();
            $table->string('locale')->nullable();
            $table->bigInteger('about_us_id')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('about_us_id')->references('id')->on('about_us')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unique(['about_us_id', 'locale']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
