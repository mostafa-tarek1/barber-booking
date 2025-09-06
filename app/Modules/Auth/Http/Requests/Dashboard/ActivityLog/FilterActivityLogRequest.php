<?php
namespace App\Modules\Auth\Http\Requests\Dashboard\ActivityLog;
use Illuminate\Foundation\Http\FormRequest;
class FilterActivityLogRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'subject_id'   => ['nullable', 'integer'],
            'subject_type' => ['nullable', 'string'],
        ];
    }
    public function authorize(): bool
    {
        return true;
    }
}
