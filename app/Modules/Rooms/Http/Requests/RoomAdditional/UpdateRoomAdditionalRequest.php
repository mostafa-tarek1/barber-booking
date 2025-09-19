<?php

namespace App\Modules\Rooms\Http\Requests\RoomAdditional;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Rooms\Application\DTOS\RoomAdditional\RoomAdditionalDTO;
use Illuminate\Validation\Rule;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdateRoomAdditionalRequest extends BaseRequestAbstract
{
    protected $dtoClass = RoomAdditionalDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function CustomRules(): array
    {
        $rules = [];

        $rules['room_additional_id'] = ['required', 'integer', 'exists:room_additionals,id'];

        $roomAdditionalId = $this->input('room_additional_id');


        $rules['image'] = [
            'nullable',
            new ImageBase64OrFileRule(),
        ];

        $rules['is_active'] = ['boolean'];

        $rules['order'] = ['nullable', 'integer', 'min:0'];

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = [
                'sometimes',
                'string',
                'min:2',
                'max:255',
                Rule::unique('room_additional_translations', 'title')
                    ->where('locale', $locale)
                    ->ignore($roomAdditionalId, 'room_additional_id'),
            ];
            $rules["translations.description_$locale"] = 'sometimes|string|min:2';
        }


        return $rules;
    }
}
