<?php

namespace App\Modules\Rooms\Http\Requests\RoomAdditional;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Rooms\Application\DTOS\RoomAdditional\RoomAdditionalDTO;
use Illuminate\Validation\Rule;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class CreateRoomAdditionalRequest extends BaseRequestAbstract
{
    protected $dtoClass = RoomAdditionalDTO::class;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function CustomRules(): array
    {
        $rules = [];

        $rules['room_id'] = ['nullable', 'exists:rooms,id'];

        $rules['parent_id'] = ['nullable', 'exists:room_additionals,id'];

        $rules['image'] = [
            'nullable',
            new ImageBase64OrFileRule(),
        ];

        $rules['is_active'] = ['boolean'];
        $rules['order'] = ['nullable', 'integer', 'min:0'];

        $rules['translations'] = ['required', 'array'];
        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('room_additional_translations', 'title')
                    ->where('locale', $locale),
            ];
        }

        return $rules;
    }
}

