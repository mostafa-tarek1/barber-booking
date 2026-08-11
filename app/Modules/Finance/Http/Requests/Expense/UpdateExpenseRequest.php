<?php

namespace App\Modules\Finance\Http\Requests\Expense;

class UpdateExpenseRequest extends StoreExpenseRequest
{
    public function rules(): array
    {
        return [
            'expense_id' => ['required', 'integer', 'exists:expenses,id'],
            ...parent::rules(),
        ];
    }
}
