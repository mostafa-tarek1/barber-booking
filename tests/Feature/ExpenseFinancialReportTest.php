<?php

namespace Tests\Feature;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\Invoice;
use App\Modules\Finance\Application\Services\FinancialReportService;
use App\Modules\Finance\Infrastructure\Persistence\Models\Expense\Expense;
use App\Modules\Statistics\Application\UseCases\Statistics\StatisticsUseCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ExpenseFinancialReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_expense_for_team_member_updates_dashboard_profit(): void
    {
        $this->travelTo(Carbon::parse('2026-08-11 12:00:00', 'Africa/Cairo'));
        $admin = $this->employee('مدير', 'admin');
        $teamMember = $this->employee('محمد', 'employee');

        $response = $this->actingAs($admin, 'employee')->postJson('/dashboard/create_expense', [
            'category' => 'meals',
            'description' => 'فطار محمد',
            'amount' => 50,
            'payment_method' => 'cash',
            'expense_date' => '2026-08-11',
            'employee_id' => $teamMember->id,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.employee.id', $teamMember->id)
            ->assertJsonPath('data.creator.id', $admin->id)
            ->assertJsonPath('data.category_label', 'وجبات وضيافة');
        $this->assertDatabaseHas('expenses', [
            'description' => 'فطار محمد',
            'amount' => 50,
            'employee_id' => $teamMember->id,
            'created_by' => $admin->id,
        ]);
        $dashboard = app(StatisticsUseCase::class)->getDashboardStatistics()->getData();
        $this->assertSame(50.0, $dashboard['today_expenses']);
        $this->assertSame(-50.0, $dashboard['today_net_profit']);
    }

    public function test_current_periods_show_revenue_expenses_and_net_profit(): void
    {
        $this->travelTo(Carbon::parse('2026-08-11 12:00:00', 'Africa/Cairo'));
        $cashier = $this->employee('كاشير', 'employee');
        $this->paidInvoice($cashier, 4000, '2026-08-11 14:00:00');
        $this->paidInvoice($cashier, 1000, '2026-08-10 14:00:00');
        $this->expense(200, 'internet', '2026-08-11', 'اشتراك الإنترنت');
        $this->expense(200, 'electricity', '2026-08-11', 'شحن الكهرباء');
        $this->expense(1000, 'salaries', '2026-08-11', 'مرتبات');
        $this->expense(100, 'supplies', '2026-08-10', 'مناديل');

        $report = app(FinancialReportService::class)->report([
            'date_from' => '2026-08-01',
            'date_to' => '2026-08-11',
        ]);

        $this->assertFinancialSummary($report['periods']['today'], 4000, 1400, 2600);
        $this->assertFinancialSummary($report['periods']['week'], 5000, 1500, 3500);
        $this->assertFinancialSummary($report['periods']['month'], 5000, 1500, 3500);
        $this->assertFinancialSummary($report['selected_period'], 5000, 1500, 3500);
        $this->assertSame([
            ['date' => '2026-08-10', 'revenue' => 1000.0, 'expenses' => 100.0, 'net_profit' => 900.0],
            ['date' => '2026-08-11', 'revenue' => 4000.0, 'expenses' => 1400.0, 'net_profit' => 2600.0],
        ], $report['daily_activity']);
        $this->assertContains([
            'category' => 'salaries',
            'label' => 'مرتبات',
            'amount' => 1000.0,
        ], $report['category_breakdown']);
    }

    public function test_payment_before_four_am_belongs_to_the_previous_business_day(): void
    {
        $cashier = $this->employee('كاشير', 'employee');
        $this->paidInvoice($cashier, 500, '2026-08-11 03:30:00');
        $this->expense(100, 'supplies', '2026-08-10', 'مستلزمات الوردية');

        $report = app(FinancialReportService::class)->report([
            'date_from' => '2026-08-10',
            'date_to' => '2026-08-10',
        ]);

        $this->assertFinancialSummary($report['selected_period'], 500, 100, 400);
        $this->assertSame('2026-08-10', $report['daily_activity'][0]['date']);
    }

    public function test_financial_summary_subtracts_expenses_from_matching_payment_method(): void
    {
        $cashier = $this->employee('كاشير', 'employee');
        $this->paidInvoice($cashier, 1000, '2026-08-11 14:00:00', [
            ['payment_method' => 'cash', 'amount' => 600],
            ['payment_method' => 'wallet', 'amount' => 400],
        ]);
        $this->paidInvoice($cashier, 500, '2026-08-11 15:00:00', [
            ['payment_method' => 'card', 'amount' => 500],
        ]);
        $this->expense(100, 'supplies', '2026-08-11', 'مناديل نقدي');
        Expense::query()->create([
            'category' => 'internet',
            'description' => 'إنترنت محفظة',
            'amount' => 50,
            'payment_method' => 'wallet',
            'expense_date' => '2026-08-11',
        ]);

        $report = app(FinancialReportService::class)->report([
            'date_from' => '2026-08-11',
            'date_to' => '2026-08-11',
        ]);

        $this->assertFinancialSummary($report['selected_period'], 1500, 150, 1350);
        $this->assertSame([
            'cash' => 600.0,
            'wallet' => 400.0,
            'card' => 500.0,
        ], $report['selected_period']['revenue_by_payment_method']);
        $this->assertSame([
            'cash' => 100.0,
            'wallet' => 50.0,
        ], $report['selected_period']['expenses_by_payment_method']);
        $this->assertSame([
            'cash' => 500.0,
            'wallet' => 350.0,
            'card' => 500.0,
        ], $report['selected_period']['net_by_payment_method']);
    }

    public function test_cashier_financial_access_is_limited_to_current_business_day(): void
    {
        $this->travelTo(Carbon::parse('2026-08-11 12:00:00', 'Africa/Cairo'));
        $cashier = $this->employee('كاشير', 'employee');
        $this->paidInvoice($cashier, 1000, '2026-08-11 14:00:00');
        $this->paidInvoice($cashier, 500, '2026-08-10 14:00:00');
        $this->expense(100, 'supplies', '2026-08-11', 'مناديل اليوم');
        $this->expense(200, 'internet', '2026-08-10', 'إنترنت أمس');

        $reportResponse = $this->actingAs($cashier, 'employee')->postJson('/dashboard/fetch_financial_report', [
            'date_from' => '2026-08-01',
            'date_to' => '2026-08-11',
        ]);
        $reportResponse
            ->assertSuccessful()
            ->assertJsonPath('data.selected_period.date_from', '2026-08-11')
            ->assertJsonPath('data.selected_period.revenue', 1000)
            ->assertJsonPath('data.selected_period.expenses', 100)
            ->assertJsonPath('data.selected_period.net_profit', 900)
            ->assertJsonMissingPath('data.periods.week')
            ->assertJsonMissingPath('data.periods.month');

        $expenseResponse = $this->actingAs($cashier, 'employee')->postJson('/dashboard/fetch_expenses', [
            'date_from' => '2026-08-10',
            'date_to' => '2026-08-10',
        ]);
        $expenseResponse
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.expenses')
            ->assertJsonPath('data.expenses.0.description', 'مناديل اليوم');
    }

    #[DataProvider('cashierExpenseDates')]
    public function test_cashier_expense_date_is_limited_to_current_business_day(
        string $expenseDate,
        int $expectedStatus,
        int $expectedExpenseCount
    ): void {
        $this->travelTo(Carbon::parse('2026-08-11 12:00:00', 'Africa/Cairo'));
        $cashier = $this->employee('كاشير', 'employee');

        $response = $this->actingAs($cashier, 'employee')->postJson('/dashboard/create_expense', [
            'category' => 'electricity',
            'description' => 'شحن الكهرباء',
            'amount' => 200,
            'payment_method' => 'cash',
            'expense_date' => $expenseDate,
        ]);

        $response->assertStatus($expectedStatus);
        $this->assertDatabaseCount('expenses', $expectedExpenseCount);
    }

    #[DataProvider('cashierModificationRequests')]
    public function test_cashier_cannot_modify_recorded_expenses(string $endpoint, array $requestAttributes): void
    {
        $this->travelTo(Carbon::parse('2026-08-11 12:00:00', 'Africa/Cairo'));
        $cashier = $this->employee('كاشير', 'employee');
        $expense = Expense::query()->create([
            'category' => 'meals',
            'description' => 'فطار الفريق',
            'amount' => 50,
            'payment_method' => 'cash',
            'expense_date' => '2026-08-11',
            'created_by' => $cashier->id,
        ]);

        $this->actingAs($cashier, 'employee')
            ->postJson("/dashboard/{$endpoint}", ['expense_id' => $expense->id, ...$requestAttributes])
            ->assertForbidden();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => 'فطار الفريق',
            'amount' => 50,
        ]);
    }

    public static function cashierExpenseDates(): array
    {
        return [
            'current business day' => ['2026-08-11', 201, 1],
            'previous business day' => ['2026-08-10', 422, 0],
        ];
    }

    public static function cashierModificationRequests(): array
    {
        return [
            'update' => ['update_expense', [
                'category' => 'meals',
                'description' => 'وصف معدل',
                'amount' => 60,
                'payment_method' => 'cash',
                'expense_date' => '2026-08-11',
            ]],
            'delete' => ['delete_expense', []],
        ];
    }

    private function employee(string $firstName, string $type): Employee
    {
        return Employee::query()->create([
            'first_name' => $firstName,
            'employee_type' => $type,
            'status' => 'active',
        ]);
    }

    private function paidInvoice(Employee $cashier, float $total, string $paidAt, array $payments = []): void
    {
        $invoice = Invoice::query()->create([
            'customer_name' => 'عميل اختبار',
            'cashier_id' => $cashier->id,
            'subtotal' => $total,
            'discount' => 0,
            'total' => $total,
            'payment_method' => 'cash',
            'status' => 'paid',
            'paid_at' => Carbon::parse($paidAt, 'Africa/Cairo'),
        ]);
        $invoice->payments()->createMany($payments ?: [[
            'payment_method' => 'cash',
            'amount' => $total,
        ]]);
    }

    private function expense(float $amount, string $category, string $date, string $description): void
    {
        Expense::query()->create([
            'category' => $category,
            'description' => $description,
            'amount' => $amount,
            'payment_method' => 'cash',
            'expense_date' => $date,
        ]);
    }

    private function assertFinancialSummary(array $summary, float $revenue, float $expenses, float $netProfit): void
    {
        $this->assertSame($revenue, $summary['revenue']);
        $this->assertSame($expenses, $summary['expenses']);
        $this->assertSame($netProfit, $summary['net_profit']);
    }
}
