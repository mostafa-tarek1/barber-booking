<?php

namespace App\Modules\Booking\Application\UseCases\Booking;

use App\Modules\Booking\Infrastructure\Persistence\Models\BookingService\BookingService;
use App\Modules\Base\Application\Response\DataFailed;
use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee as AdminEmployee;
use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Booking\Application\DTOS\Booking\BookingDTO;
use App\Modules\Booking\Application\DTOS\Booking\BookingFilterDTO;
use App\Modules\Booking\Infrastructure\Persistence\Repositories\Booking\BookingRepository;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Booking\Http\Resources\Dashboard\Booking\BookingResource as DashboardBookingResource;
use App\Modules\Booking\Http\Resources\Api\Booking\BookingResource as ApiBookingResource;
use App\Modules\Booking\Http\Resources\Dashboard\Booking\BookingDetailResource as DashboardBookingDetailResource;
use App\Modules\Booking\Http\Resources\Api\Booking\BookingDetailResource as ApiBookingDetailResource;
use App\Modules\Booking\Application\Services\BookingCacheService;
use App\Modules\Booking\Application\Services\BarberAvailabilityService;
use App\Modules\Booking\Application\Services\BookingSelectionService;
use App\Modules\Booking\Application\Services\BookingDepositService;
use App\Modules\Statistics\Application\Services\StatisticsCacheService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BookingUseCase
{
    protected BookingRepository $bookingRepository;
    protected BookingCacheService $cacheService;
    protected StatisticsCacheService $statisticsCacheService;
    protected BarberAvailabilityService $availabilityService;
    protected BookingSelectionService $selectionService;
    protected $employee;

    public function __construct(
        BookingRepository $bookingRepository,
        BookingCacheService $cacheService,
        StatisticsCacheService $statisticsCacheService,
        BarberAvailabilityService $availabilityService,
        BookingSelectionService $selectionService
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->cacheService = $cacheService;
        $this->statisticsCacheService = $statisticsCacheService;
        $this->availabilityService = $availabilityService;
        $this->selectionService = $selectionService;
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    /**
     * Fetch bookings (with filter DTO)
     */
    public function fetchBooking(BookingFilterDTO $bookingFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            // التحقق من وجود force_refresh في الطلب
            $forceRefresh = request()->has('force_refresh') && request()->boolean('force_refresh');

            if ($forceRefresh) {
                // مسح الـ cache وإعادة جلب البيانات
                $this->cacheService->clearBookingCache();
            }

            // إنشاء cache key بناءً على الفلاتر
            $filters = [
                ...$bookingFilterDto->toArray(),
                'search' => $bookingFilterDto->search,
                'date' => $bookingFilterDto->date,
                'date_from' => $bookingFilterDto->date_from,
                'date_to' => $bookingFilterDto->date_to,
                'status' => $bookingFilterDto->status,
            ];
            $barberId = $this->employee?->employee_type === 'barber' ? $this->employee->id : null;
            $cacheKey = $this->cacheService->generateListCacheKey([
                ...$filters,
                'viewer_barber_id' => $barberId,
            ]);

            $bookings = $this->cacheService->getCachedBookings($cacheKey, function () use ($bookingFilterDto, $barberId) {
                return $this->bookingRepository->filter(
                    $bookingFilterDto,
                    operator: 'like',
                    translatableFields: [],
                    paginate: $bookingFilterDto->paginate,
                    limit: $bookingFilterDto->limit,
                    whereHasRelations: $barberId
                        ? ['services' => ['booking_service.employee_id' => $barberId]]
                        : [],
                    withRelations: [
                        'services.translations',
                        'packages.translations',
                        'deposit',
                        'createdBy',
                        'updatedBy',
                    ]
                );
            });

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

            if (! $this->canViewBooking($booking)) {
                return DataFailed(false, 'ليس لديك صلاحية لعرض هذا الحجز.', 403);
            }

            // Load the relationships with the pivot data
            $booking->load([
                'services' => function ($query) {
                    $query
                        ->with('translations')
                        ->withPivot([
                            'employee_id',
                            'package_id',
                            'name_snapshot',
                            'price_snapshot',
                            'duration_snapshot',
                            'quantity',
                        ]);
                },
                'packages.translations',
                'deposit',
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

    private function canViewBooking($booking): bool
    {
        if ($this->employee?->employee_type !== 'barber') {
            return true;
        }

        return $booking->services()
            ->wherePivot('employee_id', $this->employee->id)
            ->exists();
    }

    /**
     * Create booking that may include multiple services (services array)
     *
     * services in DTO: [ ['id' => 1], ['id' => 2], ... ]
     */
    public function createBooking(
        BookingDTO $bookingDTO,
        ViewTypeEnum $view,
        BookingDepositService $depositService
    ): DataStatus
    {
        try {
            $booking = DB::transaction(function () use ($bookingDTO, $view, $depositService) {
                $selection = $this->selectionService->resolve(
                    $bookingDTO->services ?? [],
                    $bookingDTO->packages ?? []
                );
                $this->lockBookableBarber($bookingDTO->employee_id);

                $from = Carbon::parse("{$bookingDTO->date} {$bookingDTO->from_hour}");
                if (! $this->availabilityService->isDurationAvailable(
                    $bookingDTO->employee_id,
                    $bookingDTO->date,
                    $selection['duration'],
                    $from->format('H:i')
                )) {
                    throw new \DomainException('الموعد لم يعد متاحاً. اختر موعداً آخر.', 409);
                }

                $bookingDTO->created_by = $this->employee->id ?? null;
                $bookingDTO->to_hour = $from->copy()->addMinutes($selection['duration'])->format('H:i');
                $bookingDTO->status = $view === ViewTypeEnum::DASHBOARD
                    ? BookingStatusEnum::CONFIRMED->value
                    : BookingStatusEnum::PENDING->value;
                $bookingDTO->expires_at = $bookingDTO->status === BookingStatusEnum::PENDING->value
                    ? now()->addMinutes(15)
                    : null;

                $booking = $this->bookingRepository->create($bookingDTO);
                $this->persistSelection($booking->id, $bookingDTO->employee_id, $selection);
                if ($view === ViewTypeEnum::DASHBOARD) {
                    $depositService->collect($booking, $bookingDTO, $selection['total_price_cents']);
                }
                $booking->load('services');
                $this->sendBookingCreatedNotification($booking, $booking->services, $bookingDTO->employee_id);

                return $booking;
            });

            $this->cacheService->clearBookingCache();
            $this->statisticsCacheService->clearAllStatisticsCache();

            $booking->load([
                'services.translations',
                'packages.translations',
                'deposit',
                'createdBy',
                'updatedBy',
            ]);
            $resource = $this->handleBookingDetailResource($booking, $view->value);

            return DataSuccess(true, 'Booking created successfully', $resource);
        } catch (\Exception $e) {
            Log::error('Error creating booking: ' . $e->getMessage());
            return DataFailed(false, $e->getMessage(), $e->getCode() === 409 ? 409 : 400);
        }
    }


    /**
     * Update booking: can change services, employee, date/from_hour
     */
    public function updateBooking(BookingDTO $bookingDTO, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $booking = DB::transaction(function () use ($bookingDTO) {
                $booking = $this->bookingRepository->getModel()->newQuery()
                    ->with(['services', 'packages', 'invoice'])
                    ->lockForUpdate()
                    ->findOrFail($bookingDTO->booking_id);

                $nextStatus = (int) ($bookingDTO->status ?? $booking->status);
                $this->assertStatusTransition((int) $booking->status, $nextStatus);

                $selectionChanged = $bookingDTO->services !== null || $bookingDTO->packages !== null;
                if ($selectionChanged) {
                    $selection = $this->selectionService->resolve(
                        $bookingDTO->services ?? [],
                        $bookingDTO->packages ?? []
                    );
                } else {
                    $selection = [
                        'duration' => (int) $booking->services->sum(
                            fn ($service) => ($service->pivot->duration_snapshot ?? $service->duration)
                                * ($service->pivot->quantity ?? 1)
                        ),
                    ];
                }

                $employeeId = (int) ($bookingDTO->employee_id
                    ?? $booking->services->first()?->pivot->employee_id);
                $this->lockBookableBarber($employeeId);

                $date = $bookingDTO->date ?? $booking->date->format('Y-m-d');
                $fromHour = $bookingDTO->from_hour ?? $booking->from_hour;
                $from = Carbon::parse("$date $fromHour");
                $to = $from->copy()->addMinutes($selection['duration']);

                if (in_array($nextStatus, [BookingStatusEnum::PENDING->value, BookingStatusEnum::CONFIRMED->value], true)
                    && ! $this->availabilityService->isDurationAvailable(
                        $employeeId,
                        $date,
                        $selection['duration'],
                        $from->format('H:i'),
                        $booking->id
                    )) {
                    throw new \DomainException('الموعد غير متاح أو يتعارض مع إجازة الحلاق.', 409);
                }

                $oldStatus = (int) $booking->status;
                $booking->update([
                    'date' => $date,
                    'from_hour' => $from->format('H:i'),
                    'to_hour' => $to->format('H:i'),
                    'updated_by' => $this->employee->id ?? null,
                    'name' => $bookingDTO->name ?? $booking->name,
                    'phone' => $bookingDTO->phone ?? $booking->phone,
                    'email' => $bookingDTO->email ?? $booking->email,
                    'status' => $nextStatus,
                    'expires_at' => $nextStatus === BookingStatusEnum::PENDING->value
                        ? ($booking->expires_at ?? now()->addMinutes(15))
                        : null,
                ]);

                if ($selectionChanged) {
                    $this->persistSelection($booking->id, $employeeId, $selection);
                } elseif ((int) $booking->services->first()?->pivot->employee_id !== $employeeId) {
                    BookingService::query()->where('booking_id', $booking->id)->update(['employee_id' => $employeeId]);
                }

                if ($oldStatus !== $nextStatus) {
                    $this->sendBookingStatusChangeNotification($booking, $oldStatus, $nextStatus);
                }

                return $booking;
            });

            $this->cacheService->clearBookingCache();
            $this->statisticsCacheService->clearAllStatisticsCache();
            $booking->load([
                'services.translations',
                'packages.translations',
                'deposit',
                'createdBy',
                'updatedBy',
            ]);

            return DataSuccess(true, 'Booking updated successfully', $this->handleBookingDetailResource($booking, $view));
        } catch (\Exception $e) {
            Log::error('Error updating booking: '.$e->getMessage());

            return DataFailed(false, $e->getMessage(), $e->getCode() === 409 ? 409 : 400);
        }
    }

    /**
     * Delete booking
     */
    public function deleteBooking(BookingFilterDTO $bookingFilterDto): DataStatus
    {
        try {
            $result = DB::transaction(function () use ($bookingFilterDto) {
                $booking = $this->bookingRepository->getModel()->newQuery()
                    ->with('invoice')
                    ->lockForUpdate()
                    ->find($bookingFilterDto->booking_id);
                if (! $booking) {
                    throw ValidationException::withMessages(['booking_id' => 'الحجز غير موجود.']);
                }
                if ($booking->invoice) {
                    throw ValidationException::withMessages([
                        'booking_id' => 'لا يمكن إلغاء أو حذف حجز مرتبط بفاتورة.',
                    ]);
                }
                if ((int) $booking->status === BookingStatusEnum::FINISHED->value) {
                    throw ValidationException::withMessages(['booking_id' => 'لا يمكن إلغاء حجز مكتمل.']);
                }

                if ((int) $booking->status === BookingStatusEnum::CANCELLED->value) {
                    $bookingId = $booking->id;
                    $booking->forceDelete();

                    return [
                        'id' => $bookingId,
                        'status' => BookingStatusEnum::CANCELLED->value,
                        'deleted' => true,
                    ];
                }

                $oldStatus = (int) $booking->status;
                $booking->update([
                    'status' => BookingStatusEnum::CANCELLED->value,
                    'expires_at' => null,
                    'updated_by' => $this->employee->id ?? null,
                ]);
                if ($oldStatus !== BookingStatusEnum::CANCELLED->value) {
                    $this->sendBookingStatusChangeNotification(
                        $booking,
                        $oldStatus,
                        BookingStatusEnum::CANCELLED->value
                    );
                }

                return [
                    'id' => $booking->id,
                    'status' => BookingStatusEnum::CANCELLED->value,
                    'deleted' => false,
                ];
            });

            $this->cacheService->clearBookingCache();
            $this->statisticsCacheService->clearAllStatisticsCache();

            return DataSuccess(
                true,
                $result['deleted'] ? 'Booking deleted successfully' : 'Booking cancelled successfully',
                $result
            );
        } catch (\Exception $e) {
            Log::error('Error deleting booking: ' . $e->getMessage());
            return DataFailed(false, $e->getMessage(), 400);
        }
    }

    private function lockBookableBarber(int $employeeId): AdminEmployee
    {
        $barber = AdminEmployee::query()
            ->whereKey($employeeId)
            ->where('employee_type', 'barber')
            ->where('status', 'active')
            ->where('is_active', true)
            ->where('is_blocked', false)
            ->lockForUpdate()
            ->first();

        if (! $barber) {
            throw ValidationException::withMessages(['employee_id' => 'الحلاق المحدد غير متاح للحجز.']);
        }

        return $barber;
    }

    private function persistSelection(int $bookingId, int $employeeId, array $selection): void
    {
        BookingService::query()->where('booking_id', $bookingId)->delete();
        DB::table('booking_package')->where('booking_id', $bookingId)->delete();

        foreach ($selection['service_rows'] as $row) {
            BookingService::query()->create([
                'booking_id' => $bookingId,
                'employee_id' => $employeeId,
                ...$row,
            ]);
        }

        foreach ($selection['packages'] as $package) {
            DB::table('booking_package')->insert([
                'booking_id' => $bookingId,
                'package_id' => $package->id,
                'name_snapshot' => $package->translate('ar')?->name ?: $package->name,
                'price_snapshot' => $package->price,
                'duration_snapshot' => $package->duration,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function assertStatusTransition(int $currentStatus, int $nextStatus): void
    {
        $allowed = [
            BookingStatusEnum::PENDING->value => [
                BookingStatusEnum::PENDING->value,
                BookingStatusEnum::CONFIRMED->value,
                BookingStatusEnum::CANCELLED->value,
            ],
            BookingStatusEnum::CONFIRMED->value => [
                BookingStatusEnum::CONFIRMED->value,
                BookingStatusEnum::FINISHED->value,
                BookingStatusEnum::CANCELLED->value,
            ],
            BookingStatusEnum::CANCELLED->value => [BookingStatusEnum::CANCELLED->value],
            BookingStatusEnum::FINISHED->value => [BookingStatusEnum::FINISHED->value],
        ];

        if (! in_array($nextStatus, $allowed[$currentStatus] ?? [], true)) {
            throw ValidationException::withMessages(['status' => 'لا يمكن تغيير الحجز إلى هذه الحالة.']);
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
            $booking->load('services');
            $employeeId = $booking->services->first()?->pivot->employee_id;
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
