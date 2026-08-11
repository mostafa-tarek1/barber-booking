<?php

namespace App\Modules\Finance\Http\Requests\Expense;

use App\Modules\Finance\Domain\Enums\ExpenseCategoryEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FetchExpensesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'word' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', Rule::enum(ExpenseCategoryEnum::class)],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
