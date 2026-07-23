<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\Setting;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Product\Domain\Enums\TaxTypeEnum;
use App\Modules\Statistics\Application\DTOS\Setting\SettingDTO;
use Illuminate\Validation\Rule;

class CreateOrUpdateRequest extends BaseRequestAbstract
{
    protected $dtoClass = SettingDTO::class;

    public function authorize(): bool
    {
        return in_array($this->user()?->employee_type, ['admin', 'super_admin'], true);
    }

    public function CustomRules(): array
    {
        $rules = [];

        $isUpdate = $this->has('setting_id');

        if ($isUpdate) {
            $rules['Setting_id'] = 'required|exists:Settings,id';
        }

        $rules['tax_amount'] = 'required|numeric';
        $rules['tax_type'] = ['required', Rule::enum(TaxTypeEnum::class)];

        return $rules;
    }
}
