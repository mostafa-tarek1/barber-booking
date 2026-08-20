<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('whatsapp_reminder_sent_at')
                ->nullable()
                ->after('expires_at');
            $table->index(
                ['status', 'date', 'whatsapp_reminder_sent_at'],
                'bookings_whatsapp_reminder_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_whatsapp_reminder_lookup');
            $table->dropColumn('whatsapp_reminder_sent_at');
        });
    }
};
