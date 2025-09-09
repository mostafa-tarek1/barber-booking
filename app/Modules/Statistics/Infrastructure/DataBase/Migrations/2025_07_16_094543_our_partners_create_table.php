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

        Schema::create('our_partners', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->timestamps();
        });
        Schema::create('our_partner_translations', function (Blueprint $table) {
            $table->id();
            $table->string("title")->nullable();
            $table->string('locale')->nullable();
            $table->unsignedBigInteger('our_partner_id')->nullable();
            $table->foreign('our_partner_id')
                ->references('id')
                ->on('our_partners')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->unique(['our_partner_id', 'locale']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('our_partners');
    }
};
