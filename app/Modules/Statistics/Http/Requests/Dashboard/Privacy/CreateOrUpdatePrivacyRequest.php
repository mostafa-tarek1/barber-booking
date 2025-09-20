<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\Privacy;

use App\Modules\Statistics\Application\DTOS\Privacy\PrivacyDTO;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;

class CreateOrUpdatePrivacyRequest extends BaseRequestAbstract
{
    protected $dtoClass = PrivacyDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function CustomRules(): array
    {
        $rules = [];

        $isUpdate = $this->has('privacy_id');

        if ($isUpdate) {
            $rules['privacy_id'] = 'required|exists:privacy,id';
        }

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = 'nullable|string|max:255';
            $rules["translations.description_$locale"] = 'required|string|max:65535';
            $rules["translations.subtitle_$locale"] = 'nullable|string|max:65535';
        }
        return $rules;
    }
}
