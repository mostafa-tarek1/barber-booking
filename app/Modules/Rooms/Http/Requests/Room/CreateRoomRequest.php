<?php

namespace App\Modules\Rooms\Http\Requests\Room;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Base\Http\Rules\ImageBase64OrFileRule;
use App\Modules\Rooms\Application\DTOS\Room\RoomDTO;
use Illuminate\Validation\Rule;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class CreateRoomRequest extends BaseRequestAbstract
{
    protected $dtoClass = RoomDTO::class;
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
        $rules['translations'] = 'required|array';
        foreach (LaravelLocalization::getSupportedLanguagesKeys() as $locale) {
            $rules["translations.title_$locale"] = [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique('room_translations', 'title')->where('locale', $locale),
            ];
            $rules["translations.description_$locale"] = 'sometimes|string|min:2';
        }
        $rules['price'] = 'required|numeric';
        $rules['image'] = [
            'nullable',
            new ImageBase64OrFileRule(),
        ];
        return $rules;
    }
}
