<?php

namespace App\Modules\Booking\Application\UseCases\Booking;

use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Application\DTOS\BookingService\BookingServiceDTO;
use App\Modules\Booking\Application\DTOS\Booking\BookingFilterDTO;
use App\Modules\Booking\Infrastructure\Persistence\Repositories\Booking\BookingRepository;
use App\Modules\Booking\Infrastructure\Persistence\Repositories\BookingService\BookingServiceRepository;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use App\Modules\Employee\Infrastructure\Persistence\Models\Employee\Employee;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Booking\Http\Resources\Dashboard\Booking\BookingResource as DashboardBookingResource;
use App\Modules\Booking\Http\Resources\Api\Booking\BookingResource as ApiBookingResource;
use App\Modules\Booking\Http\Resources\Dashboard\Booking\BookingDetailResource as DashboardBookingDetailResource;
use App\Modules\Booking\Http\Resources\Api\Booking\BookingDetailResource as ApiBookingDetailResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingUseCase
{
    protected BookingRepository $bookingRepository;
    protected BookingServiceRepository $bookingServiceRepository;
    protected $employee;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
        $this->bookingServiceRepository = new BookingServiceRepository();
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    /**
     * Fetch bookings (with filter DTO)
     */
    public function fetchBooking(BookingFilterDTO $bookingFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $bookings = $this->bookingRepository->filter(
                $bookingFilterDto,
                operator: 'like',
                translatableFields: [],
                paginate: $bookingFilterDto->paginate,
                limit: $bookingFilterDto->limit
            );

            $resource = $this->handleBookingResource($bookings, $view, $bookingFilterDto->paginate);

            return DataSuccess(
                status: true,
                message: 'Bookings fetched successfully',
                data: $bookingFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            Log::error('Error fetching bookings: ' . $e->getMessage());
            return DataFailed(status: false, message: $e->getMessage());
        }
    }

    /**
     * Fetch single booking detail
     */
    public function fetchBookingDetails(BookingFilterDTO $bookingFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $booking = $this->bookingRepository->getById($bookingFilterDto->booking_id);

            if (!$booking) {
                return DataFailed(false, 'Booking not found');
            }

            $resource = $this->handleBookingDetailResource($booking, $view);

            return DataSuccess(true, 'Booking fetched successfully', $resource);
        } catch (\Exception $e) {
            Log::error('Error fetching booking detail: ' . $e->getMessage());
            return DataFailed(false, $e->getMessage());
        }
    }

    /**
     * Create booking that may include multiple services (services array)
     *
     * services in DTO: [ ['id' => 1], ['id' => 2], ... ]
     */
    public function createBooking(BookingDTO $bookingDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        DB::beginTransaction();
        try {
            $bookingDTO->created_by = $this->employee->id ?? null;

            // 1- التحقق من السيرفيسز
            $serviceIds = collect($bookingDTO->services)->filter()->unique()->values()->toArray();
            $services = Service::whereIn('id', $serviceIds)->where('is_active', 1)->get();

            if ($services->isEmpty() || $services->count() !== count($serviceIds)) {
                DB::rollBack();
                return DataFailed(false, 'One or more selected services are invalid or inactive.');
            }

            // 2- حساب وقت النهاية من الـ duration
            $totalDuration = (int) $services->sum('duration');
            $from = Carbon::parse("{$bookingDTO->date} {$bookingDTO->from_hour}");
            $to = (clone $from)->addMinutes($totalDuration);

            $bookingDTO->to_hour = $to->format('H:i');
            $bookingDTO->status = BookingStatusEnum::CONFIRMED->value;

            // 3- تحقق من تفرغ الموظف
            $isBusy = $this->bookingRepository->checkEmployeeAvailability(
                $bookingDTO->employee_id,
                $bookingDTO->date,
                $from->format('H:i:s'),
                $to->format('H:i:s')
            );

            if ($isBusy) {
                DB::rollBack();
                return DataFailed(false, 'Employee is already booked for this time range.');
            }

            // 4- إنشاء البوكينج
            $booking = $this->bookingRepository->create($bookingDTO);
            $booking->refresh();

            // 5- ربط السيرفيسز بالـ pivot
            $pivotData = [];
            foreach ($services as $service) {
                $pivotData[$service->id] = [
                    'employee_id' => $bookingDTO->employee_id,
                    // 'duration' => $service->duration,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $booking->services()->attach($pivotData);

            DB::commit();

            $booking->load(['services', 'createdBy', 'updatedBy']);
            $resource = $this->handleBookingDetailResource($booking, $view);

            return DataSuccess(true, 'Booking created successfully', $resource);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating booking: ' . $e->getMessage());
            return DataFailed(false, $e->getMessage());
        }
    }


    /**
     * Update booking: can change services, employee, date/from_hour
     */
    public function updateBooking(BookingDTO $bookingDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        DB::beginTransaction();
        try {
            $bookingDTO->updated_by = $this->employee->id ?? null;

            $booking = $this->bookingRepository->getById($bookingDTO->booking_id);
            if (!$booking) {
                DB::rollBack();
                return DataFailed(false, 'Booking not found.');
            }

            // if services provided -> validate & compute new to_hour
            if (!empty($bookingDTO->services)) {
                $serviceIds = collect($bookingDTO->services)->pluck('id')->filter()->unique()->values()->toArray();
                $services = Service::whereIn('id', $serviceIds)->where('is_active', 1)->get();

                if ($services->isEmpty() || count($services) !== count($serviceIds)) {
                    DB::rollBack();
                    return DataFailed(false, 'One or more selected services are invalid or inactive.');
                }

                $totalDuration = (int) $services->sum('duration');
            } else {
                // fallback to existing pivot durations sum (if you store durations) or first service duration
                $existingServices = $booking->services ?? collect();
                $totalDuration = (int) ($existingServices->sum('duration') ?: ($existingServices->first()->duration ?? 0));
                $services = $existingServices;
            }

            // determine from/time values (use incoming if provided, else current booking)
            $date = $bookingDTO->date ?? $booking->booking_date;
            $fromHour = $bookingDTO->from_hour ?? $booking->from_hour;
            $from = Carbon::parse("{$date} {$fromHour}");
            $to = (clone $from)->addMinutes($totalDuration);

            // check employee availability excluding current booking
            $employeeId = $bookingDTO->employee_id ?? ($booking->services->first()->pivot->employee_id ?? null);

            $isBusy = $this->bookingRepository->checkEmployeeAvailability(
                $employeeId,
                $date,
                $from->format('H:i:s'),
                $to->format('H:i:s'),
                $booking->id
            );

            if ($isBusy) {
                DB::rollBack();
                return DataFailed(false, 'Employee is already booked for this time range.');
            }

            // update booking main fields
            $booking->fill(array_merge($bookingDTO->toArray(), [
                'booking_date' => $date,
                'from_hour' => $from->format('H:i'),
                'to_hour' => $to->format('H:i'),
            ]))->save();

            // if services changed -> sync pivot
            if (!empty($bookingDTO->services)) {
                // remove old pivots
                $this->bookingServiceRepository->delete($booking->id);

                foreach ($services as $service) {
                    $this->bookingServiceRepository->create(new BookingServiceDTO([
                        'booking_id' => $booking->id,
                        'service_id' => $service->id,
                        'employee_id' => $employeeId,
                        'duration' => $service->duration,
                    ]));
                }
            }

            DB::commit();

            $booking->load(['services', 'creator', 'updater']);
            $resource = $this->handleBookingDetailResource($booking, $view);

            return DataSuccess(true, 'Booking updated successfully', $resource);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating booking: ' . $e->getMessage());
            return DataFailed(false, $e->getMessage());
        }
    }

    /**
     * Delete booking
     */
    public function deleteBooking(BookingFilterDTO $bookingFilterDto): DataStatus
    {
        try {
            $booking = $this->bookingRepository->getById($bookingFilterDto->booking_id);
            if (!$booking) {
                return DataFailed(false, 'Booking not found', 404);
            }

            // delete pivot entries first (safety)
            $this->bookingServiceRepository->delete($booking->id);

            $this->bookingRepository->delete($booking->id);

            return DataSuccess(true, 'Booking deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting booking: ' . $e->getMessage());
            return DataFailed(false, $e->getMessage());
        }
    }

    /**
     * Helpers for resource wrapping
     */
    private function handleBookingResource($bookings, $viewType, $paginate = false)
    {
        return match ($viewType) {
            ViewTypeEnum::DASHBOARD->value => DashboardBookingResource::collection($bookings),
            ViewTypeEnum::MOBILE->value => ApiBookingResource::collection($bookings),
            default => DashboardBookingResource::collection($bookings),
        };
    }

    private function handleBookingDetailResource($booking, $viewType)
    {
        return match ($viewType) {
            ViewTypeEnum::DASHBOARD->value => DashboardBookingDetailResource::make($booking),
            ViewTypeEnum::MOBILE->value => ApiBookingDetailResource::make($booking),
            default => DashboardBookingDetailResource::make($booking),
        };
    }
}
