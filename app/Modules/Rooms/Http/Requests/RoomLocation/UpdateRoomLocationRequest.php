<?php

namespace App\Modules\Rooms\Http\Requests\RoomLocation;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Rooms\Application\DTOS\RoomLocation\RoomLocationDTO;
use Illuminate\Validation\Rule;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class UpdateRoomLocationRequest extends BaseRequestAbstract
{
    protected $dtoClass = RoomLocationDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function customRules(): array
    {
        $rules = [];
        $rules['room_location_id'] = ['required', 'integer', 'exists:room_locations,id'];

        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = [
                'sometimes',
                'string',
                'min:2',
                'max:255',
                Rule::unique('room_location_translations', 'title')
                    ->where('locale', $locale)
                    ->ignore($this->room_location_id, 'room_location_id'),
            ];
            $rules["translations.description_$locale"] = 'sometimes|string|min:2';
        }
        $rules['price'] = 'nullable|numeric';
        return $rules;
    }
}
