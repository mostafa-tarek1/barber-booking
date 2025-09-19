<?php

namespace App\Modules\Rooms\Http\Requests\Room;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Rooms\Application\DTOS\Room\RoomDTO;
use Illuminate\Validation\Rule;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdateRoomRequest extends BaseRequestAbstract
{
    protected $dtoClass = RoomDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function customRules(): array
    {
        $rules = [];
        $rules['room_id'] = ['required', 'integer', 'exists:rooms,id'];

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = [
                'sometimes',
                'string',
                'min:2',
                'max:255',
                Rule::unique('room_translations', 'title')
                    ->where('locale', $locale)
                    ->ignore($this->room_id, 'room_id'),
            ];
            $rules["translations.description_$locale"] = 'sometimes|string|min:2';
        }
        $rules['price'] = 'nullable|numeric';
        $rules['image'] = [
            'nullable',
            new ImageBase64OrFileRule(),
        ];
        return $rules;
    }
}
