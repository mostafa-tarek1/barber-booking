<?php

namespace Tests\Feature;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\Invoice;
use App\Modules\Finance\Application\Services\FinancialReportService;
use App\Modules\Finance\Infrastructure\Persistence\Models\Expense\Expense;
use App\Modules\Statistics\Application\UseCases\Statistics\StatisticsUseCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    private function employee(string $firstName, string $type): Employee
    {
        return Employee::query()->create([
            'first_name' => $firstName,
            'employee_type' => $type,
            'status' => 'active',
        ]);
    }

    private function paidInvoice(Employee $cashier, float $total, string $paidAt): void
    {
        Invoice::query()->create([
            'customer_name' => 'عميل اختبار',
            'cashier_id' => $cashier->id,
            'subtotal' => $total,
            'discount' => 0,
            'total' => $total,
            'payment_method' => 'cash',
            'status' => 'paid',
            'paid_at' => Carbon::parse($paidAt, 'Africa/Cairo'),
        ]);
    }

    private function expense(float $amount, string $category, string $date, string $description): void
    {
        Expense::query()->create([
            'category' => $category,
            'description' => $description,
            'amount' => $amount,
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
