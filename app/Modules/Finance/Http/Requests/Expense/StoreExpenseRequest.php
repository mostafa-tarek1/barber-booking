<?php

namespace App\Modules\Finance\Http\Requests\Expense;

use App\Modules\Finance\Domain\Enums\ExpenseCategoryEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::enum(ExpenseCategoryEnum::class)],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'expense_date' => ['required', 'date'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
        ];
    }
}
