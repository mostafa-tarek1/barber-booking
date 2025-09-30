<?php

namespace App\Modules\Booking\Application\UseCases\Booking;

use App\Modules\Booking\Infrastructure\Persistence\Models\BookingService\BookingService;
use App\Modules\Base\Application\Response\DataFailed;
use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee as AdminEmployee;
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

            // Load the relationships with the pivot data
            $booking->load([
                'services' => function ($query) {
                    $query->withPivot(['employee_id']);
                }
            ]);

            // Get the employee from the first service if available
            if ($booking->services->isNotEmpty()) {
                $employeeId = $booking->services->first()->pivot->employee_id;
                $employee = \App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee::find($employeeId);
                $booking->setRelation('employee', $employee);
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
            // 1.5- التحقق من الموظف
            $employee = AdminEmployee::where('id', $bookingDTO->employee_id)
                ->where('status', 'active')
                ->first();

            if (!$employee) {
                DB::rollBack();
                return DataFailed(false, 'Selected employee is inactive or does not exist.');
            }

            // 2- حساب وقت النهاية من الـ duration
            $totalDuration = (int) $services->sum('duration');
            $from = Carbon::parse("{$bookingDTO->date} {$bookingDTO->from_hour}");
            $to = (clone $from)->addMinutes($totalDuration);
            $requestedDuration = $totalDuration; // Store the duration for later use

            // Check if booking time is outside allowed hours (1 PM to 1 AM)
            $startHour = $from->copy()->setTime(13, 0, 0); // 1:00 PM
            $endHour = $from->copy()->addDay()->setTime(1, 0, 0); // 1:00 AM next day

            if ($from->lt($startHour) || $to->gt($endHour) || $to->lt($from)) {
                DB::rollBack();
                return DataFailed(false, 'يُسمح بالحجز من الساعة 1 ظهراً حتى 1 صباحاً فقط.');
            }

            $bookingDTO->to_hour = $to->format('H:i');
            $bookingDTO->status = BookingStatusEnum::PENDING->value;

            // 3- تحقق من تفرغ الموظف
            $isBusy = $this->bookingRepository->checkEmployeeAvailability(
                $bookingDTO->employee_id,
                $bookingDTO->date,
                $from->format('H:i:s'),
                $to->format('H:i:s')
            );

            if ($isBusy) {
                DB::rollBack();

                // Get suggested time slots from the repository
                $suggestedSlots = $this->bookingRepository->getAvailableTimeSlots(
                    $bookingDTO->employee_id,
                    $bookingDTO->date,
                    '13:00', // 1:00 PM - Shift start
                    '01:00', // 1:00 AM - Shift end (next day)
                    $requestedDuration // Duration in minutes
                );

                // Prepare the response with the suggested slots
                $response = [
                    'message' => 'الموظف محجز بالفعل في هذا التوقيت.'
                ];

                if (!empty($suggestedSlots)) {
                    $response['suggested_slots'] = array_map(function ($index, $slot) {
                        return [
                            'id' => $index + 1,
                            'from' => Carbon::parse($slot['from'])->format('g:i A'),
                            'to' => Carbon::parse($slot['to'])->format('g:i A'),
                            'from_time' => $slot['from'],
                            'to_time' => $slot['to']
                        ];
                    }, array_keys($suggestedSlots), array_values($suggestedSlots));
                }

                return new DataFailed(
                    $response,
                    false,
                    400
                );
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

            // إرسال إشعار تلقائي
            $this->sendBookingCreatedNotification($booking, $services, $bookingDTO->employee_id);

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
            if (empty($bookingDTO->booking_id)) {
                return DataFailed(false, 'Booking ID is required.');
            }

            $booking = $this->bookingRepository->getById($bookingDTO->booking_id);
            if (!$booking) {
                return DataFailed(false, 'Booking not found.', 404);
            }

            $bookingDTO->updated_by = $this->employee->id ?? null;

            // Process services if provided
            $services = collect();
            $totalDuration = 0;

            if (!empty($bookingDTO->services)) {
                // Handle both array of IDs and array of objects
                $serviceIds = collect($bookingDTO->services)
                    ->map(function ($item) {
                        return is_array($item) ? ($item['id'] ?? $item) : $item;
                    })
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();

                if (empty($serviceIds)) {
                    return DataFailed(false, 'No valid services provided.');
                }

                $services = Service::whereIn('id', $serviceIds)
                    ->where('is_active', 1)
                    ->get();

                if ($services->isEmpty() || $services->count() !== count($serviceIds)) {
                    return DataFailed(false, 'One or more selected services are invalid or inactive.');
                }

                $totalDuration = (int) $services->sum('duration');
            } else {
                // Use existing services if not provided in update
                $services = $booking->services()->withPivot(['employee_id', 'created_at', 'updated_at'])->get();
                if ($services->isEmpty()) {
                    return DataFailed(false, 'No services found for this booking.');
                }
                $totalDuration = (int) $services->sum('duration');
            }

            // Validate employee
            $employeeId = $bookingDTO->employee_id ?? ($services->first()->pivot->employee_id ?? null);

            if (!$employeeId) {
                return DataFailed(false, 'Employee is required for the booking.');
            }

            $employee = AdminEmployee::where('id', $employeeId)
                ->where('status', 'active')
                ->first();

            if (!$employee) {
                return DataFailed(false, 'Selected employee is not available.');
            }

            // Handle date and time
            $date = $bookingDTO->date ?? $booking->booking_date;
            $fromHour = $bookingDTO->from_hour ?? $booking->from_hour;

            try {
                $from = Carbon::parse("{$date} {$fromHour}");
                $to = (clone $from)->addMinutes($totalDuration);

                // Check if booking time is outside allowed hours (1 PM to 1 AM)
                $startHour = $from->copy()->setTime(13, 0, 0); // 1:00 PM
                $endHour = $from->copy()->addDay()->setTime(1, 0, 0); // 1:00 AM next day

                if ($from->lt($startHour) || $to->gt($endHour) || $to->lt($from)) {
                    return DataFailed(false, 'يُسمح بالحجز من الساعة 1 ظهراً حتى 1 صباحاً فقط.');
                }

            } catch (\Exception $e) {
                return DataFailed(false, 'تنسيق التاريخ أو الوقت غير صالح.');
            }

            // Check employee availability
            $isBusy = $this->bookingRepository->checkEmployeeAvailability(
                $employeeId,
                $date,
                $from->format('H:i:s'),
                $to->format('H:i:s'),
                $booking->id // Exclude current booking from availability check
            );

            if ($isBusy) {
                // Get suggested time slots
                $suggestedSlots = $this->getAvailableTimeSlots(
                    $employeeId,
                    $date,
                    $from->format('H:i:s'),
                    $to->format('H:i:s')
                );

                // Format the suggested slots as a string
                $suggestedSlotsStr = '';
                if (!empty($suggestedSlots)) {
                    $formattedSlots = [];
                    foreach ($suggestedSlots as $index => $slot) {
                        $formattedSlots[] = sprintf(
                            '%s. من %s إلى %s',
                            $index + 1,
                            date('g:i A', strtotime($slot['from'])),
                            date('g:i A', strtotime($slot['to']))
                        );
                    }

                    return new DataFailed(
                        [
                            'message' => 'الموظف محجز بالفعل في هذا التوقيت.',
                            'suggested_slots' => $formattedSlots
                        ],
                        false,
                        400
                    );
                }

                return new DataFailed(
                    ['message' => 'الموظف محجز بالفعل في هذا التوقيت.'],
                    false,
                    400
                );
            }

            // Update booking with all fields from DTO
            $updateData = [
                'booking_date' => $date,
                'from_hour' => $from->format('H:i'),
                'to_hour' => $to->format('H:i'),
                'updated_by' => $bookingDTO->updated_by,
                // Include name, phone, email if provided in DTO
                'name' => $bookingDTO->name ?? $booking->name,
                'phone' => $bookingDTO->phone ?? $booking->phone,
                'email' => $bookingDTO->email ?? $booking->email,
                // Update status if provided
                'status' => $bookingDTO->status ?? $booking->status,
            ];

            // حفظ الحالة القديمة قبل التحديث
            $oldStatus = $booking->status;

            $booking->update($updateData);

            // إرسال إشعارات حسب تغيير الحالة
            if ($oldStatus != $updateData['status']) {
                $this->sendBookingStatusChangeNotification($booking, $oldStatus, $updateData['status']);
            }

            // Update services if changed
            if (!empty($bookingDTO->services)) {
                // Delete existing service associations
                $this->bookingServiceRepository->delete($booking->id);

                // Create new service associations
                foreach ($services as $service) {
                    // First, create the booking service record directly
                    // Note: The booking_service table doesn't have a duration column
                    $bookingService = new BookingService();
                    $bookingService->booking_id = $booking->id;
                    $bookingService->service_id = $service->id;
                    $bookingService->employee_id = $employeeId;
                    $bookingService->save();
                }
            }

            DB::commit();

            // Load relationships with the correct method names
            $booking->load(['services', 'createdBy', 'updatedBy']);
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

            // إرسال إشعار قبل الحذف
            $this->sendBookingDeletedNotification($booking);

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

    /**
     * إرسال إشعار إنشاء حجز جديد
     */
    private function sendBookingCreatedNotification($booking, $services, $employeeId)
    {
        try {
            // استخدام Event لإرسال الإشعار
            event(new \App\Modules\Notification\Application\Events\BookingCreated(
                $booking,
                $services->toArray(),
                $employeeId
            ));
        } catch (\Exception $e) {
            Log::error('Failed to send booking created notification: ' . $e->getMessage());
        }
    }

    /**
     * إرسال إشعار تغيير حالة الحجز
     */
    private function sendBookingStatusChangeNotification($booking, $oldStatus, $newStatus)
    {
        try {
            $booking->load(['services', 'employee']);
            $employeeId = $booking->employee ? $booking->employee->id : null;
            $serviceNames = $booking->services->pluck('name')->join(', ');

            // تحديد نوع الإشعار حسب الحالة الجديدة
            $eventClass = match ($newStatus) {
                BookingStatusEnum::CONFIRMED->value => \App\Modules\Notification\Application\Events\BookingConfirmed::class,
                BookingStatusEnum::CANCELLED->value => \App\Modules\Notification\Application\Events\BookingCancelled::class,
                BookingStatusEnum::FINISHED->value => \App\Modules\Notification\Application\Events\BookingFinished::class,
                default => null
            };

            if ($eventClass) {
                event(new $eventClass($booking));
            }

            // إرسال إشعار إضافي للموظف إذا تغيرت الحالة
            if ($employeeId && $oldStatus != $newStatus) {
                $message = match ($newStatus) {
                    BookingStatusEnum::CONFIRMED->value => "تم تأكيد حجز العميل {$booking->name} لخدمة {$serviceNames}",
                    BookingStatusEnum::CANCELLED->value => "تم إلغاء حجز العميل {$booking->name} لخدمة {$serviceNames}",
                    BookingStatusEnum::FINISHED->value => "تم إكمال خدمة {$serviceNames} للعميل {$booking->name}",
                    default => "تم تحديث حالة حجز العميل {$booking->name}"
                };

                $this->sendDirectNotificationToEmployee(
                    $employeeId,
                    "تحديث حالة الحجز",
                    $message,
                    [
                        'booking_id' => $booking->id,
                        'customer_name' => $booking->name,
                        'service_names' => $serviceNames,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'date' => $booking->date?->format('Y-m-d'),
                        'time' => $booking->from_hour
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error('Failed to send booking status change notification: ' . $e->getMessage());
        }
    }

    /**
     * إرسال إشعار مباشر للموظف
     */
    private function sendDirectNotificationToEmployee($employeeId, $title, $message, $data = [])
    {
        try {
            $notificationService = app(\App\Modules\Notification\Application\Services\NotificationService::class);

            $type = match (true) {
                str_contains($message, 'تأكيد') => \App\Modules\Notification\Domain\Enums\NotificationTypeEnum::BOOKING_CONFIRMED,
                str_contains($message, 'إلغاء') => \App\Modules\Notification\Domain\Enums\NotificationTypeEnum::BOOKING_CANCELLED,
                str_contains($message, 'إكمال') => \App\Modules\Notification\Domain\Enums\NotificationTypeEnum::BOOKING_FINISHED,
                default => \App\Modules\Notification\Domain\Enums\NotificationTypeEnum::SYSTEM_ALERT
            };

            $notificationService->sendToEmployee(
                $employeeId,
                $type,
                $title,
                $message,
                $data
            );
        } catch (\Exception $e) {
            Log::error('Failed to send direct notification: ' . $e->getMessage());
        }
    }

    /**
     * إرسال إشعار حذف الحجز
     */
    private function sendBookingDeletedNotification($booking)
    {
        try {
            $booking->load(['services', 'employee']);
            $employeeId = $booking->employee ? $booking->employee->id : null;
            $serviceNames = $booking->services->pluck('name')->join(', ');

            if ($employeeId) {
                $message = "تم حذف حجز العميل {$booking->name} لخدمة {$serviceNames}";

                $this->sendDirectNotificationToEmployee(
                    $employeeId,
                    "حذف الحجز",
                    $message,
                    [
                        'booking_id' => $booking->id,
                        'customer_name' => $booking->name,
                        'service_names' => $serviceNames,
                        'date' => $booking->date?->format('Y-m-d'),
                        'time' => $booking->from_hour,
                        'action' => 'deleted'
                    ]
                );
            }

        } catch (\Exception $e) {
            Log::error('Failed to send booking deleted notification: ' . $e->getMessage());
        }
    }

    /**
     * Get available time slots for an employee on a specific date
     *
     * @param int $employeeId
     * @param string $date
     * @param string $fromTime
     * @param string $toTime
     * @param int $intervalMinutes
     * @return array
     */
    public function getAvailableTimeSlots(
        int $employeeId,
        string $date,
        string $fromTime,
        string $toTime,
        int $intervalMinutes = 30
    ): array {
        try {
            Log::info('=== START: Getting available slots ===');
            Log::info('Input parameters:', [
                'employee_id' => $employeeId,
                'date' => $date,
                'from_time' => $fromTime,
                'to_time' => $toTime,
                'interval_minutes' => $intervalMinutes
            ]);

            // Get available slots for the whole working day
            $availableSlots = $this->bookingRepository->getAvailableTimeSlots(
                $employeeId,
                $date
            );

            Log::info('Available slots from repository:', [
                'count' => is_array($availableSlots) ? count($availableSlots) : 'not an array',
                'slots' => $availableSlots
            ]);

            // If no available slots, return empty array
            if (empty($availableSlots)) {
                Log::warning('No available slots found for employee ' . $employeeId . ' on date ' . $date);
                return [];
            }

            // Parse the requested time range
            $requestedStart = Carbon::parse($fromTime);
            $requestedEnd = Carbon::parse($toTime);
            $requestedDuration = $requestedStart->diffInMinutes($requestedEnd);

            Log::info('Requested booking details', [
                'start' => $requestedStart->format('H:i:s'),
                'end' => $requestedEnd->format('H:i:s'),
                'duration_minutes' => $requestedDuration
            ]);

            $suggestedSlots = [];
            $workingStart = Carbon::parse('13:00:00'); // 1:00 PM
            $workingEnd = Carbon::parse('01:00:00')->addDay(); // 1:00 AM next day
            $currentSlot = Carbon::parse($date . ' ' . $workingStart->format('H:i:s'));
            $endOfDay = Carbon::parse($date . ' ' . $workingEnd->format('H:i:s'));

            Log::info('Slot generation parameters:', [
                'working_start' => $workingStart->format('H:i:s'),
                'working_end' => $workingEnd->format('H:i:s'),
                'current_slot' => $currentSlot->format('Y-m-d H:i:s'),
                'end_of_day' => $endOfDay->format('Y-m-d H:i:s'),
                'requested_duration' => $requestedDuration . ' minutes'
            ]);

            $slotCounter = 0;
            $maxSlotsToCheck = 100; // Safety limit to prevent infinite loops

            while ($currentSlot->lt($endOfDay) && $slotCounter < $maxSlotsToCheck) {
                $slotCounter++;
                $slotEnd = $currentSlot->copy()->addMinutes($requestedDuration);

                Log::debug('Checking slot #' . $slotCounter, [
                    'current_slot' => $currentSlot->format('H:i:s'),
                    'slot_end' => $slotEnd->format('H:i:s')
                ]);

                // Skip if slot goes beyond working hours
                if ($slotEnd->gt($endOfDay)) {
                    Log::debug('Skipping slot - extends beyond working hours');
                    $currentSlot->addMinutes($intervalMinutes);
                    continue;
                }

                $isAvailable = true;
                $conflictingSlot = null;

                // Check if this slot is available
                foreach ($availableSlots as $slot) {
                    $slotStart = Carbon::parse($slot['from']);
                    $slotEndTime = Carbon::parse($slot['to']);

                    // Check for overlap
                    if ($currentSlot->lt($slotEndTime) && $slotStart->lt($slotEnd)) {
                        $isAvailable = false;
                        $conflictingSlot = [
                            'from' => $slotStart->format('H:i:s'),
                            'to' => $slotEndTime->format('H:i:s')
                        ];
                        break;
                    }
                }

                if ($isAvailable) {
                    $newSlot = [
                        'from' => $currentSlot->format('H:i:s'),
                        'to' => $slotEnd->format('H:i:s'),
                    ];

                    $suggestedSlots[] = $newSlot;
                    Log::debug('Added suggested slot', $newSlot);

                    // Skip ahead to avoid overlapping suggestions
                    $currentSlot = $slotEnd;

                    // Limit the number of suggestions
                    if (count($suggestedSlots) >= 5) {
                        Log::info('Reached maximum number of suggested slots (5)');
                        break;
                    }
                } else {
                    Log::debug('Slot not available - conflicts with', $conflictingSlot);
                    $currentSlot->addMinutes($intervalMinutes);
                }
            }

            if ($slotCounter >= $maxSlotsToCheck) {
                Log::warning('Reached maximum number of slot checks (' . $maxSlotsToCheck . ')');
            }

            Log::info('Generated ' . count($suggestedSlots) . ' suggested time slots');
            return $suggestedSlots;
        } catch (\Exception $e) {
            Log::error('Error getting available time slots: ' . $e->getMessage());
            return [];
        }
    }
}
