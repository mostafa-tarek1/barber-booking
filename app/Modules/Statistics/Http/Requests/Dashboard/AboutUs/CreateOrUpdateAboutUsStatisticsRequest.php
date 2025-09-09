<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\AboutUs;

use App\Modules\Statistics\Application\DTOS\AboutUs\AboutUsStatisticsDTO;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrUpdateAboutUsStatisticsRequest extends FormRequest
{

        public function toDTO(): AboutUsStatisticsDTO
    {
        return new AboutUsStatisticsDTO($this->validated());
    }
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [];

        $isUpdate = $this->has('about_us_statistics_id');

        if ($isUpdate) {
            $rules['about_us_statistics_id'] = 'required|exists:about_us_statistics,id';
        }

        $rules['about_us_id'] = 'required|exists:about_us,id';
        $rules['key']   = 'nullable|string|max:255';
        $rules['value'] = 'nullable|string|max:65535';
        $rules['icon']  = 'nullable|string|max:255';

        return $rules;
    }
}
