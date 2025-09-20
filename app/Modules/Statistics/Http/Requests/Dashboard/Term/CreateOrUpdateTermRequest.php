<?php

namespace App\Modules\Statistics\Http\Requests\Dashboard\Term;

use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Statistics\Application\DTOS\Term\TermDTO;

class CreateOrUpdateTermRequest extends BaseRequestAbstract
{
    protected $dtoClass = TermDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function CustomRules(): array
    {
        $rules = [];

        $isUpdate = $this->has('Term_id');

        if ($isUpdate) {
            $rules['Term_id'] = 'required|exists:Terms,id';
        }

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = 'nullable|string|max:255';
            $rules["translations.description_$locale"] = 'required|string|max:65535';
            $rules["translations.subtitle_$locale"] = 'nullable|string|max:65535';
        }
        return $rules;
    }
}
