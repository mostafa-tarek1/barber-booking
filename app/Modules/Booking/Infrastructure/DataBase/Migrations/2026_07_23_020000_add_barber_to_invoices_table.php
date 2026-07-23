<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('barber_id')
                ->nullable()
                ->after('cashier_id')
                ->constrained('employees')
                ->nullOnDelete();
            $table->index(['barber_id', 'paid_at', 'status']);
        });

        DB::table('invoices')
            ->orderBy('id')
            ->eachById(function (object $invoice) {
                $barberId = $invoice->booking_id
                    ? DB::table('booking_service')
                        ->where('booking_id', $invoice->booking_id)
                        ->whereNotNull('employee_id')
                        ->value('employee_id')
                    : null;
                $barberId ??= DB::table('invoice_items')
                    ->where('invoice_id', $invoice->id)
                    ->whereNotNull('employee_id')
                    ->value('employee_id');

                if ($barberId) {
                    DB::table('invoices')->where('id', $invoice->id)->update(['barber_id' => $barberId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['barber_id', 'paid_at', 'status']);
            $table->dropConstrainedForeignId('barber_id');
        });
    }
};
