<?php

namespace App\Modules\Booking\Http\Requests\Booking;

use App\Modules\Base\Domain\Request\BaseRequestAbstract;
use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends BaseRequestAbstract
{
    protected $dtoClass = BookingDTO::class;

    public function authorize(): bool
    {
        return true;
    }

    public function CustomRules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'phone' => ['nullable', 'string', 'min:9', 'max:13'],
            'email' => ['nullable', 'email', 'max:255'],
            'date' => ['nullable', 'date', 'after_or_equal:today'],
            'name' => ['nullable', 'string', 'max:255'],
            'from_hour' => ['nullable', 'date_format:H:i'],
            'to_hour' => ['nullable', 'date_format:H:i', 'after:from_hour'],
            'status' => ['sometimes', Rule::enum(BookingStatusEnum::class)],

            'room_id' => ['sometimes', 'exists:rooms,id'],
            'additionals' => ['nullable', 'array'],
            'additionals.*' => ['exists:room_additionals,id']
        ];
    }

    /* public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $rooms = $this->input('rooms', []);
            if (!empty($rooms)) {
                foreach ($rooms as $index => $room) {
                    if (!empty($room['room_additional_id'])) {
                        foreach ((array)$room['room_additional_id'] as $additionalId) {
                            $exists = DB::table('room_additionals')
                                ->where('id', $additionalId)
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
                }
            }
        });
    } */
}
