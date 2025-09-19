<?php
namespace App\Modules\Booking\Http\Requests\Booking;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends BaseRequestAbstract
{
    protected $dtoClass = BookingDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function CustomRules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'min:9', 'max:13'],
            'email' => ['nullable', 'email', 'max:255'],
            'date' => ['nullable', 'date', 'after_or_equal:today'],
            'name' => ['nullable', 'string', 'max:255'],
            'from_hour' => ['nullable', 'date_format:H:i'],
            'to_hour' => ['nullable', 'date_format:H:i', 'after:from_hour'],
            'status' => ['sometimes', Rule::enum(BookingStatusEnum::class)],

            'room_id' => ['required', 'exists:rooms,id'],
            'additionals' => ['nullable', 'array'],
            'additionals.*' => ['exists:room_additionals,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $rooms = $this->input('rooms', []);

            foreach ($rooms as $index => $room) {
                if (!empty($room['room_additional_id'])) {
                    $exists = DB::table('room_additionals')
                        ->where('id', $room['room_additional_id'])
                        ->where('room_id', $room['room_id'])
                        ->exists();

                    if (!$exists) {
                        $validator->errors()->add(
                            "rooms.$index.room_additional_id",
                            "The selected room_additional_id is invalid for the given room_id."
                        );
                    }
                }
            }
        });
    }
}
