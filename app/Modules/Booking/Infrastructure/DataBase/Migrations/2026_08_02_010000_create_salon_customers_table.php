<?php

use App\Support\PhoneNumber;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salon_customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('normalized_phone')->unique();
            $table->timestamps();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('salon_customer_id')
                ->nullable()
                ->after('booking_id')
                ->constrained('salon_customers')
                ->nullOnDelete();
        });

        $this->backfillCustomers();
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('salon_customer_id');
        });

        Schema::dropIfExists('salon_customers');
    }

    private function backfillCustomers(): void
    {
        DB::table('invoices')
            ->select(['id', 'customer_name', 'customer_phone', 'paid_at'])
            ->whereNotNull('customer_phone')
            ->where('customer_phone', '!=', '')
            ->orderBy('id')
            ->chunkById(200, fn ($invoices) => $this->backfillInvoiceChunk($invoices));
    }

    private function backfillInvoiceChunk($invoices): void
    {
        foreach ($invoices as $invoice) {
            $normalizedPhone = PhoneNumber::normalize($invoice->customer_phone);
            if (! $normalizedPhone) {
                continue;
            }

            $customerId = $this->upsertCustomer(
                $invoice->customer_name,
                $invoice->customer_phone,
                $normalizedPhone,
                $invoice->paid_at
            );
            DB::table('invoices')->where('id', $invoice->id)->update(['salon_customer_id' => $customerId]);
        }
    }

    private function upsertCustomer(string $name, string $phone, string $normalizedPhone, $paidAt): int
    {
        DB::table('salon_customers')->upsert([[
            'name' => $name,
            'phone' => $phone,
            'normalized_phone' => $normalizedPhone,
            'created_at' => $paidAt,
            'updated_at' => $paidAt,
        ]], ['normalized_phone'], ['name', 'phone', 'updated_at']);

        return DB::table('salon_customers')->where('normalized_phone', $normalizedPhone)->value('id');
    }
};
