<?php

namespace App\Modules\Booking\Application\UseCases\Booking;

use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Application\DTOS\BookingRooms\BookingRoomsDTO;
use App\Modules\Booking\Infrastructure\Persistence\Repositories\Booking\BookingRepository;
use App\Modules\Booking\Application\DTOS\Booking\BookingFilterDTO;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Booking\Infrastructure\Persistence\Repositories\BookingRooms\BookingRoomRepository;
use App\Modules\Rooms\Infrastructure\Persistence\Models\RoomAdditional\RoomAdditional;
use Illuminate\Support\Facades\DB;
use App\Modules\Booking\Http\Resources\Dashboard\Booking\BookingResource as DashboardBookingResource;
use App\Modules\Booking\Http\Resources\Api\Booking\BookingResource as ApiBookingResource;
use App\Modules\Booking\Http\Resources\Dashboard\Booking\BookingDetailResource as DashboardBookingDetailResource;
use App\Modules\Booking\Http\Resources\Api\Booking\BookingDetailResource as ApiBookingDetailResource;
use App\Modules\Rooms\Infrastructure\Persistence\Models\Room\Room;
use Illuminate\Support\Facades\Log;

class BookingUseCase
{
    protected $bookingRoomRepository;
    protected $bookingRepository;
    protected $employee;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
        $this->bookingRoomRepository = new BookingRoomRepository();
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchBooking(BookingFilterDTO $bookingFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $bookings = $this->bookingRepository->filter(
                $bookingFilterDto,
                operator: 'like',
                translatableFields: ['title', 'description'],
                paginate: $bookingFilterDto->paginate,
                limit: $bookingFilterDto->limit
            );

            $resource = $this->HandelBookingResource(
                $bookings,
                $view,
                $bookingFilterDto->paginate
            );

            return DataSuccess(
                status: true,
                message: 'Booking fetched successfully',
                data: $bookingFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(status: false, message: $e->getMessage());
        }
    }

    public function fetchBookingDetails(BookingFilterDTO $bookingFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $booking = $this->bookingRepository->getById($bookingFilterDto->booking_id);

            $resource = $this->HandelBookingDetailResource(
                $booking,
                $view,
                false
            );

            return DataSuccess(
                status: true,
                message: 'Booking fetched successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            return DataFailed(status: false, message: $e->getMessage());
        }
    }

    public function createBooking(BookingDTO $bookingDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        DB::beginTransaction();

        try {
            $bookingDTO->created_by = $this->employee->id ?? null;

            $room = Room::with('roomAdditionals')
                ->findOrFail($bookingDTO->room_id);

                /* room no longer related to room_additionals */
            /* if (!empty($bookingDTO->additionals)) {
                $validAdditionalIds = $room->roomAdditionals->pluck('id')->toArray();
                $invalidAdditionals = array_diff($bookingDTO->additionals, $validAdditionalIds);

                if (!empty($invalidAdditionals)) {
                    DB::rollBack();
                    return DataFailed(
                        status: false,
                        message: 'One or more selected additional services are not available for this room.'
                    );
                }
            } */

            // check room availability
            $isBooked = $this->bookingRepository->checkRoomAvailability(
                $bookingDTO->room_id,
                $bookingDTO->date,
                $bookingDTO->from_hour,
                $bookingDTO->to_hour
            );

            if ($isBooked) {
                DB::rollBack();
                return DataFailed(
                    status: false,
                    message: "Room is already booked in this period"
                );
            }

            // create booking
            $booking = $this->bookingRepository->create($bookingDTO);
            $booking->refresh();

            // attach room + additionals
            if (!empty($bookingDTO->additionals)) {
                foreach ($bookingDTO->additionals as $additionalId) {
                    $this->bookingRoomRepository->create(new BookingRoomsDTO([
                        'booking_id' => $booking->id,
                        'room_id' => $bookingDTO->room_id,
                        'room_additional_id' => $additionalId,
                    ]));
                }
            } else {
                $this->bookingRoomRepository->create(new BookingRoomsDTO([
                    'booking_id' => $booking->id,
                    'room_id' => $bookingDTO->room_id,
                    'room_additional_id' => null,
                ]));
            }

            DB::commit();

            $resource = $this->HandelBookingDetailResource(
                $booking->load(['rooms', 'creator', 'updater']),
                $view,
                false
            );

            return DataSuccess(
                status: true,
                message: 'Booking created successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return DataFailed(status: false, message: $e->getMessage());
        }
    }

    public function updateBooking(BookingDTO $bookingDTO): DataStatus
    {
        DB::beginTransaction();

        try {
            // Update booking
            $bookingDTO->updated_by = $this->employee->id ?? null;

            // Get the booking first to ensure it exists
            $booking = $this->bookingRepository->getById($bookingDTO->booking_id);

            // Update booking fields
            $booking->fill($bookingDTO->toArray());
            $booking->save();
            $booking->refresh();

            // Update room and additionals if room_id is provided
            if (isset($bookingDTO->room_id)) {
                // Get the room with its valid additionals
                $room = Room::with('roomAdditionals')
                    ->findOrFail($bookingDTO->room_id);

                // Validate additionals belong to the room
                /* if (!empty($bookingDTO->additionals)) {
                    $validAdditionalIds = $room->roomAdditionals->pluck('id')->toArray();
                    $invalidAdditionals = array_diff($bookingDTO->additionals, $validAdditionalIds);

                    if (!empty($invalidAdditionals)) {
                        throw new \Exception('One or more selected additional services are not available for this room.');
                    }
                } */

                // Detach all current rooms and additionals
                $booking->rooms()->detach();

                // Attach new room + additionals
                if (!empty($bookingDTO->additionals)) {
                    foreach ($bookingDTO->additionals as $additionalId) {
                        $this->bookingRoomRepository->create(new BookingRoomsDTO([
                            'booking_id' => $booking->id,
                            'room_id' => $bookingDTO->room_id,
                            'room_additional_id' => $additionalId,
                        ]));
                    }
                } else {
                    $this->bookingRoomRepository->create(new BookingRoomsDTO([
                        'booking_id' => $booking->id,
                        'room_id' => $bookingDTO->room_id,
                        'room_additional_id' => null,
                    ]));
                }
            }

            DB::commit();

            // Reload the booking with all relationships
            $booking->load(['rooms', 'creator', 'updater']);

            return DataSuccess(
                status: true,
                message: 'Booking updated successfully',
                data: new DashboardBookingResource($booking)
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating booking: ' . $e->getMessage());
            return DataFailed(
                status: false,
                message: 'Failed to update booking: ' . $e->getMessage()
            );
        }
    }

    public function deleteBooking(BookingFilterDTO $bookingFilterDto): DataStatus
    {
        try {
            $booking = $this->bookingRepository->getById($bookingFilterDto->booking_id);

            if (!$booking) {
                return DataFailed(status: false, statusCode: 404, message: 'Booking not found');
            }

            if ($booking->children && $booking->children->count() > 0) {
                return DataFailed(status: false, statusCode: 400, message: 'Booking has children');
            }

            $this->bookingRepository->delete($bookingFilterDto->booking_id);

            return DataSuccess(status: true, message: 'Booking deleted successfully');
        } catch (\Exception $e) {
            return DataFailed(status: false, message: $e->getMessage());
        }
    }

    private function HandelBookingResource($bookings, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return DashboardBookingResource::collection($bookings);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return ApiBookingResource::collection($bookings);
        } else {
            return DashboardBookingResource::collection($bookings);
        }
    }

    public function HandelBookingDetailResource($booking, $viewType, $paginate)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return DashboardBookingDetailResource::make($booking);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return ApiBookingResource::make($booking);
        } else {
            return DashboardBookingDetailResource::make($booking);
        }
    }
}
