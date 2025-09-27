<?php

namespace App\Modules\Booking\Application\UseCases\Booking;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Application\DTOS\BookingService\BookingServiceDTO;
use App\Modules\Booking\Application\DTOS\Booking\BookingFilterDTO;
use App\Modules\Booking\Infrastructure\Persistence\Repositories\Booking\BookingRepository;
use App\Modules\BookingService\Infrastructure\Persistence\Repositories\BookingService\BookingServiceRepository;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Http\Resources\Dashboard\Booking\BookingResource as DashboardBookingResource;
use App\Modules\Booking\Http\Resources\Api\Booking\BookingResource as ApiBookingResource;
use App\Modules\Booking\Http\Resources\Dashboard\Booking\BookingDetailResource as DashboardBookingDetailResource;
use App\Modules\Booking\Http\Resources\Api\Booking\BookingDetailResource as ApiBookingDetailResource;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
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

            $resource = $this->HandelBookingResource($bookings, $view);

            return DataSuccess(true, 'Booking fetched successfully', $bookingFilterDto->paginate ? $resource->response()->getData() : $resource);
        } catch (\Exception $e) {
            return DataFailed(false, $e->getMessage());
        }
    }

    public function fetchBookingDetails(BookingFilterDTO $bookingFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $booking = $this->bookingRepository->getById($bookingFilterDto->booking_id);

            if (!$booking) {
                return DataFailed(false, 'Booking not found');
            }

            $resource = $this->HandelBookingDetailResource($booking, $view);

            return DataSuccess(true, 'Booking fetched successfully', $resource);
        } catch (\Exception $e) {
            return DataFailed(false, $e->getMessage());
        }
    }

    public function createBooking(BookingDTO $bookingDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        DB::beginTransaction();
        try {
            $bookingDTO->created_by = $this->employee->id ?? null;

            // جلب الخدمة والموظف
            $service = Service::findOrFail($bookingDTO->service_id);
            $employee = Employee::findOrFail($bookingDTO->employee_id);

            // حساب وقت البداية والنهاية بناءً على مدة الخدمة
            $from = Carbon::parse("{$bookingDTO->date} {$bookingDTO->from_hour}");
            $to = (clone $from)->addMinutes($service->duration);

            // تحقق من توفر الموظف
            $isBooked = $this->bookingRepository->checkEmployeeAvailability(
                $employee->id,
                $bookingDTO->date,
                $from->format('H:i:s'),
                $to->format('H:i:s')
            );

            if ($isBooked) {
                DB::rollBack();
                return DataFailed(false, "Employee is not available at this time.");
            }

            // إنشاء الحجز
            $bookingDTO->to_hour = $to->format('H:i:s');
            $bookingDTO->status = BookingStatusEnum::CONFIRMED->value;
            $booking = $this->bookingRepository->create($bookingDTO);

            // ربط الخدمة بالموظف في جدول pivot
            $this->bookingServiceRepository->create(new BookingServiceDTO([
                'booking_id' => $booking->id,
                'service_id' => $service->id,
                'employee_id' => $employee->id,
                'duration' => $service->duration,
            ]));

            DB::commit();

            $resource = $this->HandelBookingDetailResource($booking->load(['services', 'creator', 'updater']), $view);

            return DataSuccess(true, 'Booking created successfully', $resource);
        } catch (\Exception $e) {
            DB::rollBack();
            return DataFailed(false, $e->getMessage());
        }
    }

    public function updateBooking(BookingDTO $bookingDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        DB::beginTransaction();
        try {
            $bookingDTO->updated_by = $this->employee->id ?? null;

            $booking = $this->bookingRepository->getById($bookingDTO->booking_id);
            if (!$booking) {
                return DataFailed(false, 'Booking not found');
            }

            // تحديث البيانات الأساسية
            $booking->fill($bookingDTO->toArray())->save();

            if (isset($bookingDTO->service_id) && isset($bookingDTO->employee_id)) {
                $service = Service::findOrFail($bookingDTO->service_id);
                $employee = Employee::findOrFail($bookingDTO->employee_id);

                $from = Carbon::parse("{$bookingDTO->date} {$bookingDTO->from_hour}");
                $to = (clone $from)->addMinutes($service->duration);

                // تحقق من توفر الموظف
                $isBooked = $this->bookingRepository->checkEmployeeAvailability(
                    $employee->id,
                    $bookingDTO->date,
                    $from->format('H:i:s'),
                    $to->format('H:i:s'),
                    $booking->id
                );

                if ($isBooked) {
                    DB::rollBack();
                    return DataFailed(false, "Employee is not available at this time.");
                }

                $booking->to_hour = $to->format('H:i:s');
                $booking->save();

                // تحديث جدول pivot
                $booking->services()->detach();
                $this->bookingServiceRepository->create(new BookingServiceDTO([
                    'booking_id' => $booking->id,
                    'service_id' => $service->id,
                    'employee_id' => $employee->id,
                    'duration' => $service->duration,
                ]));
            }

            DB::commit();

            $booking->load(['services', 'creator', 'updater']);
            $resource = $this->HandelBookingDetailResource($booking, $view);

            return DataSuccess(true, 'Booking updated successfully', $resource);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating booking: ' . $e->getMessage());
            return DataFailed(false, 'Failed to update booking: ' . $e->getMessage());
        }
    }

    public function deleteBooking(BookingFilterDTO $bookingFilterDto): DataStatus
    {
        try {
            $booking = $this->bookingRepository->getById($bookingFilterDto->booking_id);

            if (!$booking) {
                return DataFailed(false, 'Booking not found', 404);
            }

            $this->bookingRepository->delete($bookingFilterDto->booking_id);

            return DataSuccess(true, 'Booking deleted successfully');
        } catch (\Exception $e) {
            return DataFailed(false, $e->getMessage());
        }
    }

    private function HandelBookingResource($bookings, $viewType)
    {
        return match ($viewType) {
            ViewTypeEnum::DASHBOARD->value => DashboardBookingResource::collection($bookings),
            ViewTypeEnum::MOBILE->value => ApiBookingResource::collection($bookings),
            default => DashboardBookingResource::collection($bookings),
        };
    }

    private function HandelBookingDetailResource($booking, $viewType)
    {
        return match ($viewType) {
            ViewTypeEnum::DASHBOARD->value => DashboardBookingDetailResource::make($booking),
            ViewTypeEnum::MOBILE->value => ApiBookingDetailResource::make($booking),
            default => DashboardBookingDetailResource::make($booking),
        };
    }
}
