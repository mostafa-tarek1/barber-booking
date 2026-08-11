<?php

namespace App\Modules\Finance\Http\Resources\Expense;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

class ExpenseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'description' => $this->description,
            'amount' => $this->amount,
            'expense_date' => $this->expense_date->format('Y-m-d'),
            'employee' => $this->employeeSummary(),
            'creator' => $this->creatorSummary(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }

    private function employeeSummary(): array|MissingValue|null
    {
        return $this->whenLoaded('employee', fn () => $this->employee ? [
            'id' => $this->employee->id,
            'name' => trim($this->employee->first_name.' '.$this->employee->last_name),
        ] : null);
    }

    private function creatorSummary(): array|MissingValue|null
    {
        return $this->whenLoaded('creator', fn () => $this->creator ? [
            'id' => $this->creator->id,
            'name' => trim($this->creator->first_name.' '.$this->creator->last_name),
        ] : null);
    }
}
