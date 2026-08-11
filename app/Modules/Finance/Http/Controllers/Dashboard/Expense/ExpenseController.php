<?php

namespace App\Modules\Finance\Http\Controllers\Dashboard\Expense;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Application\Services\FinancialReportService;
use App\Modules\Finance\Http\Requests\Expense\FetchExpensesRequest;
use App\Modules\Finance\Http\Requests\Expense\FinancialReportRequest;
use App\Modules\Finance\Http\Requests\Expense\StoreExpenseRequest;
use App\Modules\Finance\Http\Requests\Expense\UpdateExpenseRequest;
use App\Modules\Finance\Http\Resources\Expense\ExpenseResource;
use App\Modules\Finance\Infrastructure\Persistence\Models\Expense\Expense;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(FetchExpensesRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $expenses = $this->filteredExpenses($filters)
            ->latest('expense_date')
            ->latest('id')
            ->paginate($filters['limit'] ?? 20);

        return response()->json([
            'status' => 200,
            'message' => 'تم جلب المصروفات بنجاح',
            'data' => [
                'expenses' => ExpenseResource::collection($expenses->items())->resolve(),
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'total' => $expenses->total(),
            ],
        ]);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = Expense::query()->create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ])->load(['employee:id,first_name,last_name', 'creator:id,first_name,last_name']);

        return response()->json([
            'status' => 201,
            'message' => 'تم تسجيل المصروف بنجاح',
            'data' => ExpenseResource::make($expense)->resolve(),
        ], 201);
    }

    public function update(UpdateExpenseRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $expense = Expense::query()->findOrFail($validated['expense_id']);
        unset($validated['expense_id']);
        $expense->update($validated);
        $expense->load(['employee:id,first_name,last_name', 'creator:id,first_name,last_name']);

        return response()->json([
            'status' => 200,
            'message' => 'تم تحديث المصروف بنجاح',
            'data' => ExpenseResource::make($expense)->resolve(),
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expense_id' => ['required', 'integer', 'exists:expenses,id'],
        ]);
        Expense::query()->findOrFail($validated['expense_id'])->delete();

        return response()->json(['status' => 200, 'message' => 'تم حذف المصروف بنجاح']);
    }

    public function report(FinancialReportRequest $request, FinancialReportService $reportService): JsonResponse
    {
        return response()->json([
            'status' => 200,
            'message' => 'تم جلب التقرير المالي بنجاح',
            'data' => $reportService->report($request->validated()),
        ]);
    }

    private function filteredExpenses(array $filters): Builder
    {
        return Expense::query()
            ->with(['employee:id,first_name,last_name', 'creator:id,first_name,last_name'])
            ->when($filters['word'] ?? null, fn ($query, $word) => $query->where('description', 'like', "%{$word}%"))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['employee_id'] ?? null, fn ($query, $employeeId) => $query->where('employee_id', $employeeId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('expense_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('expense_date', '<=', $date));
    }
}
