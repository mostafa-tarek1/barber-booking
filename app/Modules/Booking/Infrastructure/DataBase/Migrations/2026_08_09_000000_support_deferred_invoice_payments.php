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
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'void'])
                ->default('paid')
                ->change();
            $table->timestamp('paid_at')->nullable()->change();
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->index('invoice_id');
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropUnique(['invoice_id', 'payment_method']);
        });
    }

    public function down(): void
    {
        $this->mergeRepeatedPaymentMethods();

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->unique(['invoice_id', 'payment_method']);
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropIndex(['invoice_id']);
        });

        DB::table('invoices')
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->update(['status' => 'void', 'paid_at' => DB::raw('COALESCE(paid_at, created_at)')]);

        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', ['paid', 'void'])->default('paid')->change();
            $table->timestamp('paid_at')->nullable(false)->change();
        });
    }

    private function mergeRepeatedPaymentMethods(): void
    {
        DB::table('invoice_payments')
            ->select('invoice_id', 'payment_method')
            ->selectRaw('MIN(id) as retained_id, SUM(amount) as total_amount')
            ->groupBy('invoice_id', 'payment_method')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->each(function (object $payments) {
                DB::table('invoice_payments')
                    ->where('id', $payments->retained_id)
                    ->update(['amount' => $payments->total_amount, 'updated_at' => now()]);
                DB::table('invoice_payments')
                    ->where('invoice_id', $payments->invoice_id)
                    ->where('payment_method', $payments->payment_method)
                    ->where('id', '!=', $payments->retained_id)
                    ->delete();
            });
    }
};
