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
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("organization_id")->nullable();
            $table->unsignedBigInteger("employee_id")->nullable();
            $table->timestamps();
        });
        Schema::create('term_translations', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->longText("description")->nullable();
            $table->string('subtitle')->nullable(); 
            $table->string('locale')->nullable();
            $table->bigInteger('term_id')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('term_id')->references('id')->on('terms')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unique(['term_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms');
        Schema::dropIfExists('term_translations');
    }
};
