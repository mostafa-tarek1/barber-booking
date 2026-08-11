<?php

namespace App\Modules\Finance\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class FinancialReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'required_with:date_to', 'date'],
            'date_to' => ['nullable', 'required_with:date_from', 'date', 'after_or_equal:date_from'],
        ];
    }
}
