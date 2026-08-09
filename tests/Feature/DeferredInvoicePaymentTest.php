<?php

namespace Tests\Feature;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Application\Services\BookingDepositService;
use App\Modules\Booking\Application\Services\InvoicePaymentService;
use App\Modules\Booking\Application\Services\InvoiceService;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\Invoice;
use App\Modules\Statistics\Application\UseCases\Statistics\StatisticsUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DeferredInvoicePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_deferred_invoice_enters_sales_only_after_full_payment(): void
    {
        $invoice = $this->deferredInvoice();

        $this->assertSame('unpaid', $invoice->status);
        $this->assertNull($invoice->paid_at);
        $this->assertSame(0.0, $this->totalRevenue());

        $invoice = app(InvoicePaymentService::class)->collect($invoice, [
            ['payment_method' => 'cash', 'amount' => 40],
        ]);

        $this->assertSame('partially_paid', $invoice->status);
        $this->assertSame('40.00', $invoice->paid_amount);
        $this->assertSame('60.00', $invoice->remaining_amount);
        $this->assertSame(0.0, $this->totalRevenue());

        $invoice = app(InvoicePaymentService::class)->collect($invoice, [
            ['payment_method' => 'cash', 'amount' => 60],
        ]);

        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);
        $this->assertSame('100.00', $invoice->paid_amount);
        $this->assertSame('0.00', $invoice->remaining_amount);
        $this->assertSame(100.0, $this->totalRevenue());
        $this->assertDatabaseCount('invoice_payments', 2);
    }

    public function test_payment_above_remaining_balance_is_rejected(): void
    {
        $invoice = $this->deferredInvoice();

        $this->expectException(ValidationException::class);

        app(InvoicePaymentService::class)->collect($invoice, [
            ['payment_method' => 'wallet', 'amount' => 100.01],
        ]);
    }

    public function test_booking_deposit_becomes_the_first_payment_on_deferred_invoice(): void
    {
        [$cashier, $barber] = $this->employees();
        $booking = Booking::query()->create(['name' => 'عميل بديبوزيت', 'status' => 1]);
        app(BookingDepositService::class)->collect(
            $booking,
            BookingDTO::fromArray([
                'deposit_amount' => 30,
                'deposit_payment_method' => 'wallet',
            ]),
            10000
        );

        $invoice = app(InvoiceService::class)->create(
            $this->invoiceInput($barber->id, $booking->id),
            $cashier->id
        );

        $this->assertSame('partially_paid', $invoice->status);
        $this->assertSame('30.00', $invoice->paid_amount);
        $this->assertSame('70.00', $invoice->remaining_amount);
        $this->assertDatabaseHas('invoice_payments', [
            'invoice_id' => $invoice->id,
            'payment_method' => 'wallet',
            'amount' => 30,
        ]);
    }

    public function test_immediate_invoice_remains_paid_and_counted_in_sales(): void
    {
        [$cashier, $barber] = $this->employees();
        $invoiceInput = $this->invoiceInput($barber->id);
        $invoiceInput['settlement_type'] = 'immediate';
        $invoiceInput['payments'] = [['payment_method' => 'card', 'amount' => 100]];

        $invoice = app(InvoiceService::class)->create($invoiceInput, $cashier->id);

        $this->assertSame('paid', $invoice->status);
        $this->assertSame('100.00', $invoice->paid_amount);
        $this->assertSame(100.0, $this->totalRevenue());
    }

    private function deferredInvoice(): Invoice
    {
        [$cashier, $barber] = $this->employees();

        return app(InvoiceService::class)->create($this->invoiceInput($barber->id), $cashier->id);
    }

    private function employees(): array
    {
        $cashier = Employee::query()->create([
            'first_name' => 'كاشير',
            'employee_type' => 'employee',
            'status' => 'active',
        ]);
        $barber = Employee::query()->create([
            'first_name' => 'مصفف',
            'employee_type' => 'barber',
            'status' => 'active',
        ]);

        return [$cashier, $barber];
    }

    private function invoiceInput(int $barberId, ?int $bookingId = null): array
    {
        return [
            'booking_id' => $bookingId,
            'barber_id' => $barberId,
            'customer_name' => 'عميل آجل',
            'customer_phone' => null,
            'items' => [[
                'description' => 'خدمة اختبار',
                'unit_price' => 100,
                'quantity' => 1,
            ]],
            'discount' => 0,
            'settlement_type' => 'deferred',
            'notes' => null,
        ];
    }

    private function totalRevenue(): float
    {
        return (float) app(StatisticsUseCase::class)
            ->getDashboardStatistics()
            ->getData()['total_revenue'];
    }
}
