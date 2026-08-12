<?php

namespace App\Modules\Finance\Infrastructure\Persistence\Models\Expense;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Finance\Domain\Enums\ExpenseCategoryEnum;
use App\Modules\Finance\Domain\Enums\ExpensePaymentMethodEnum;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'category',
        'description',
        'amount',
        'payment_method',
        'expense_date',
        'employee_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'category' => ExpenseCategoryEnum::class,
            'payment_method' => ExpensePaymentMethodEnum::class,
            'amount' => 'decimal:2',
            'expense_date' => 'date:Y-m-d',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
