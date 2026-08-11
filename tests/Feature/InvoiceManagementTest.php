<?php

namespace Tests\Feature;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\Invoice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_replace_invoice_details_and_payments(): void
    {
        $superAdmin = $this->employee('مدير النظام', 'super_admin');
        $cashier = $this->employee('كاشير', 'employee');
        $barber = $this->employee('مصفف', 'barber');
        $invoice = $this->invoice($cashier, $barber);
        $oldItemId = $invoice->items()->create($this->invoiceItem())->id;
        $oldPaymentId = $invoice->payments()->create([
            'payment_method' => 'cash',
            'amount' => 100,
        ])->id;

        $this->actingAs($superAdmin, 'employee')
            ->postJson('/dashboard/update_invoice', [
                'invoice_id' => $invoice->id,
                'barber_id' => $barber->id,
                'customer_name' => 'عميل معدل',
                'customer_phone' => null,
                'items' => [[
                    'description' => 'خدمة معدلة',
                    'unit_price' => 75,
                    'quantity' => 2,
                ]],
                'discount' => 30,
                'payments' => [[
                    'payment_method' => 'card',
                    'amount' => 50,
                ]],
                'notes' => 'مراجعة السوبر أدمن',
            ])
            ->assertSuccessful()
            ->assertJsonPath('data.customer_name', 'عميل معدل')
            ->assertJsonPath('data.status', 'partially_paid');

        $this->assertDatabaseMissing('invoice_items', ['id' => $oldItemId]);
        $this->assertDatabaseMissing('invoice_payments', ['id' => $oldPaymentId]);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'description' => 'خدمة معدلة',
            'quantity' => 2,
            'total' => 150,
        ]);
        $this->assertDatabaseHas('invoice_payments', [
            'invoice_id' => $invoice->id,
            'payment_method' => 'card',
            'amount' => 50,
        ]);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'subtotal' => 150,
            'discount' => 30,
            'total' => 120,
            'status' => 'partially_paid',
        ]);
    }

    public function test_super_admin_deletion_removes_invoice_relations_and_reopens_booking(): void
    {
        $superAdmin = $this->employee('مدير النظام', 'super_admin');
        $cashier = $this->employee('كاشير', 'employee');
        $barber = $this->employee('مصفف', 'barber');
        $booking = Booking::query()->create([
            'name' => 'عميل حجز',
            'status' => BookingStatusEnum::FINISHED->value,
        ]);
        $invoice = $this->invoice($cashier, $barber, $booking);
        $invoiceItemId = $invoice->items()->create($this->invoiceItem())->id;
        $invoicePaymentId = $invoice->payments()->create([
            'payment_method' => 'cash',
            'amount' => 100,
        ])->id;

        $this->actingAs($superAdmin, 'employee')
            ->postJson('/dashboard/delete_invoice', ['invoice_id' => $invoice->id])
            ->assertSuccessful();

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
        $this->assertDatabaseMissing('invoice_items', ['id' => $invoiceItemId]);
        $this->assertDatabaseMissing('invoice_payments', ['id' => $invoicePaymentId]);
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatusEnum::CONFIRMED->value,
            'updated_by' => $superAdmin->id,
        ]);
    }

    #[DataProvider('unauthorizedInvoiceActions')]
    public function test_non_super_admin_cannot_modify_invoices(string $role, string $endpoint): void
    {
        $actor = $this->employee('مستخدم الإدارة', $role);
        $cashier = $this->employee('كاشير', 'employee');
        $barber = $this->employee('مصفف', 'barber');
        $invoice = $this->invoice($cashier, $barber);

        $this->actingAs($actor, 'employee')
            ->postJson("/dashboard/{$endpoint}", ['invoice_id' => $invoice->id])
            ->assertForbidden();

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }

    public static function unauthorizedInvoiceActions(): array
    {
        return [
            'admin update' => ['admin', 'update_invoice'],
            'admin delete' => ['admin', 'delete_invoice'],
            'cashier update' => ['employee', 'update_invoice'],
            'cashier delete' => ['employee', 'delete_invoice'],
            'barber update' => ['barber', 'update_invoice'],
            'barber delete' => ['barber', 'delete_invoice'],
        ];
    }

    public function test_cashier_barber_sales_report_is_limited_to_current_business_day(): void
    {
        $this->travelTo(Carbon::parse('2026-08-11 12:00:00', 'Africa/Cairo'));
        $cashier = $this->employee('كاشير', 'employee');
        $barber = $this->employee('مصفف', 'barber');
        $this->invoice($cashier, $barber, null, ['total' => 120, 'paid_at' => '2026-08-11 08:00:00']);
        $this->invoice($cashier, $barber, null, ['total' => 80, 'paid_at' => '2026-08-12 02:00:00']);
        $this->invoice($cashier, $barber, null, ['total' => 500, 'paid_at' => '2026-08-10 12:00:00']);

        $response = $this->actingAs($cashier, 'employee')
            ->postJson('/dashboard/fetch_barber_sales_report', [
                'date_from' => '2026-08-01',
                'date_to' => '2026-08-31',
            ])
            ->assertSuccessful();

        $barberSales = collect($response->json('data'))->firstWhere('id', $barber->id);

        $this->assertNotNull($barberSales);
        $this->assertSame(2, (int) $barberSales['clients_count']);
        $this->assertSame(200.0, (float) $barberSales['total_sales']);
    }

    private function employee(string $firstName, string $role): Employee
    {
        return Employee::query()->create([
            'first_name' => $firstName,
            'employee_type' => $role,
            'status' => 'active',
        ]);
    }

    private function invoice(Employee $cashier, Employee $barber, ?Booking $booking = null, array $attributes = []): Invoice
    {
        return Invoice::query()->create(array_merge([
            'booking_id' => $booking?->id,
            'customer_name' => 'عميل اختبار',
            'cashier_id' => $cashier->id,
            'barber_id' => $barber->id,
            'subtotal' => 100,
            'discount' => 0,
            'total' => 100,
            'payment_method' => 'cash',
            'status' => 'paid',
            'paid_at' => now(),
        ], $attributes));
    }

    private function invoiceItem(): array
    {
        return [
            'description' => 'خدمة أصلية',
            'unit_price' => 100,
            'quantity' => 1,
            'total' => 100,
        ];
    }
}
