<?php

namespace App\Modules\Finance\Application\Services;

use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\Invoice;
use App\Modules\Booking\Infrastructure\Persistence\Models\Invoice\InvoicePayment;
use App\Modules\Finance\Domain\Enums\ExpenseCategoryEnum;
use App\Modules\Finance\Infrastructure\Persistence\Models\Expense\Expense;
use App\Support\BusinessDay;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class FinancialReportService
{
    public function report(array $filters): array
    {
        [$dateFrom, $dateTo] = $this->selectedDates($filters);
        $selectedSummary = $this->summaryFor($dateFrom, $dateTo);

        return $this->reportDetails($dateFrom, $dateTo, $this->currentPeriodSummaries(), $selectedSummary);
    }

    public function todayReport(): array
    {
        $currentBusinessDate = BusinessDay::currentDate();
        $todaySummary = $this->summaryFor($currentBusinessDate, $currentBusinessDate);

        return $this->reportDetails(
            $currentBusinessDate,
            $currentBusinessDate,
            ['today' => $todaySummary],
            $todaySummary
        );
    }

    private function reportDetails(string $dateFrom, string $dateTo, array $periods, array $selectedSummary): array
    {
        return [
            'periods' => $periods,
            'selected_period' => $selectedSummary,
            'daily_activity' => $this->dailyActivity($dateFrom, $dateTo),
            'category_breakdown' => $this->categoryBreakdown($dateFrom, $dateTo),
            'category_options' => ExpenseCategoryEnum::options(),
        ];
    }

    private function selectedDates(array $filters): array
    {
        $currentDate = Carbon::parse(BusinessDay::currentDate(), 'Africa/Cairo');

        return [
            $filters['date_from'] ?? $currentDate->copy()->startOfMonth()->toDateString(),
            $filters['date_to'] ?? $currentDate->toDateString(),
        ];
    }

    private function currentPeriodSummaries(): array
    {
        $currentDate = Carbon::parse(BusinessDay::currentDate(), 'Africa/Cairo');
        $weekStart = $currentDate->copy()->startOfWeek(CarbonInterface::SATURDAY);

        return [
            'today' => $this->summaryFor($currentDate->toDateString(), $currentDate->toDateString()),
            'week' => $this->summaryFor($weekStart->toDateString(), $weekStart->copy()->addDays(6)->toDateString()),
            'month' => $this->summaryFor($currentDate->copy()->startOfMonth()->toDateString(), $currentDate->copy()->endOfMonth()->toDateString()),
        ];
    }

    private function summaryFor(string $dateFrom, string $dateTo): array
    {
        $revenue = $this->paidRevenue($dateFrom, $dateTo);
        $expenses = $this->expensesTotal($dateFrom, $dateTo);

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'revenue' => $revenue,
            'revenue_by_payment_method' => $this->revenueByPaymentMethod($dateFrom, $dateTo),
            'expenses' => $expenses,
            'net_profit' => $revenue - $expenses,
        ];
    }

    private function dailyActivity(string $dateFrom, string $dateTo): array
    {
        $revenueByDate = $this->revenueByDate($dateFrom, $dateTo);
        $expensesByDate = $this->expensesByDate($dateFrom, $dateTo);
        $activeDates = $revenueByDate->keys()->merge($expensesByDate->keys())->unique()->sort();

        return $activeDates->map(function (string $date) use ($revenueByDate, $expensesByDate) {
            $revenue = (float) ($revenueByDate[$date] ?? 0);
            $expenses = (float) ($expensesByDate[$date] ?? 0);

            return compact('date', 'revenue', 'expenses') + ['net_profit' => $revenue - $expenses];
        })->values()->all();
    }

    private function revenueByDate(string $dateFrom, string $dateTo): Collection
    {
        [$start] = BusinessDay::range($dateFrom);
        [, $end] = BusinessDay::range($dateTo);

        return Invoice::query()
            ->where('status', 'paid')
            ->where('paid_at', '>=', $start)
            ->where('paid_at', '<', $end)
            ->get(['paid_at', 'total'])
            ->groupBy(fn (Invoice $invoice) => BusinessDay::dateFor($invoice->paid_at))
            ->map(fn ($invoices) => (float) $invoices->sum('total'));
    }

    private function expensesByDate(string $dateFrom, string $dateTo): Collection
    {
        return Expense::query()
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->get(['expense_date', 'amount'])
            ->groupBy(fn (Expense $expense) => $expense->expense_date->format('Y-m-d'))
            ->map(fn ($expenses) => (float) $expenses->sum('amount'));
    }

    private function categoryBreakdown(string $dateFrom, string $dateTo): array
    {
        return Expense::query()
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->selectRaw('category, SUM(amount) as amount')
            ->groupBy('category')
            ->orderByDesc('amount')
            ->get()
            ->map(fn (Expense $expense) => [
                'category' => $expense->category->value,
                'label' => $expense->category->label(),
                'amount' => (float) $expense->amount,
            ])
            ->all();
    }

    private function paidRevenue(string $dateFrom, string $dateTo): float
    {
        [$start] = BusinessDay::range($dateFrom);
        [, $end] = BusinessDay::range($dateTo);

        return (float) Invoice::query()
            ->where('status', 'paid')
            ->where('paid_at', '>=', $start)
            ->where('paid_at', '<', $end)
            ->sum('total');
    }

    private function expensesTotal(string $dateFrom, string $dateTo): float
    {
        return (float) Expense::query()
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->sum('amount');
    }

    private function revenueByPaymentMethod(string $dateFrom, string $dateTo): array
    {
        [$start] = BusinessDay::range($dateFrom);
        [, $end] = BusinessDay::range($dateTo);
        $paymentTotals = InvoicePayment::query()
            ->join('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
            ->where('invoices.status', 'paid')
            ->where('invoices.paid_at', '>=', $start)
            ->where('invoices.paid_at', '<', $end)
            ->selectRaw('invoice_payments.payment_method, SUM(invoice_payments.amount) as total')
            ->groupBy('invoice_payments.payment_method')
            ->pluck('total', 'payment_method');

        return [
            'cash' => (float) ($paymentTotals['cash'] ?? 0),
            'wallet' => (float) ($paymentTotals['wallet'] ?? 0),
            'card' => (float) ($paymentTotals['card'] ?? 0),
        ];
    }
}
