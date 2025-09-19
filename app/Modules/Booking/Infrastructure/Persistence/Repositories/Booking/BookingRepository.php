<?php

namespace App\Modules\Booking\Infrastructure\Persistence\Repositories\Booking;


use Exception;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Modules\Base\Domain\DTO\BaseDTOInterface;
use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Booking\Domain\Enums\BookingStatusEnum;
use App\Modules\Booking\Infrastructure\Persistence\Models\Booking\Booking;
use App\Modules\Rooms\Infrastructure\Persistence\Models\Room\Room;
use Illuminate\Support\Collection;

class BookingRepository extends BaseRepositoryAbstract
{
    public function __construct()
    {
        $this->setModel(new Booking());
    }

    /**
     * Filter records dynamically, supporting translations and array values
     */
    public function filter(
        BaseDTOInterface $dto,
        string $operator = 'like',
        array $translatableFields = [],
        $paginate = false,
        $limit = 10,
        array $whereHasRelations = [],
        array $whereHasMultipleRelations = []
    ): Collection|LengthAwarePaginator {
        try {
            $query = $this->getModel()->query();

            $query->when(isset($dto->word), function ($q) use ($dto, $translatableFields, $operator) {
                $q->where(function ($subQuery) use ($dto, $translatableFields, $operator) {
                    foreach ($translatableFields as $field) {
                        $subQuery->orWhereTranslationLike($field, "%{$dto->word}%");
                    }
                });
            });

            foreach ($dto->toArray() as $key => $value) {
                if (!in_array($key, ['lat', 'lng', 'distance']) && filled($value)) {
                    $query
                        ->when(in_array($key, $translatableFields), fn($q) => $q->whereTranslationLike($key, "%{$value}%"))
                        ->when(is_array($value), fn($q) => $q->whereIn($key, $value))
                        ->when(is_bool($value), fn($q) => $q->where($key, $value))
                        ->when(is_numeric($value) && !in_array($key, $translatableFields), fn($q) => $q->where($key, $operator, $value))
                        ->when(is_string($value) && !in_array($key, $translatableFields), fn($q) => $q->where($key, $operator, ($operator === 'like' ? "%{$value}%" : $value)));
                }
            }

            foreach ($whereHasRelations as $relation => $conditions) {
                $query->whereHas($relation, function ($q) use ($conditions) {
                    foreach ($conditions as $key => $values) {
                        is_array($values)
                            ? $q->whereIn($key, $values)
                            : $q->where($key, $values);
                    }
                });
            }

            foreach ($whereHasMultipleRelations as $relationsGroup) {
                foreach ($relationsGroup as $relation => $conditions) {
                    $query->whereHas($relation, function ($q) use ($conditions) {
                        foreach ($conditions as $key => $values) {
                            is_array($values)
                                ? $q->whereIn($key, $values)
                                : $q->where($key, $values);
                        }
                    });
                }
            }

            if (isset($dto->lat) && isset($dto->lng)) {
                $haversine = "(
                6371 * acos(
                    cos(radians(?)) *
                    cos(radians(lat)) *
                    cos(radians(lng) - radians(?)) +
                    sin(radians(?)) *
                    sin(radians(lat))
                )
            )";

                $query->selectRaw("*, {$haversine} AS distance", [
                    $dto->lat,
                    $dto->lng,
                    $dto->lat
                ]);

                if (isset($dto->distance)) {
                    $query->having('distance', '<=', $dto->distance);
                }

                $query->orderBy('distance', 'asc');
            }

            // OrderBy default
            $orderBy = $dto->orderBy ?? 'id';
            $direction = $dto->direction ?? 'desc';

            return $paginate
                ? $query->orderBy($orderBy, $direction)->paginate($limit)
                : $query->orderBy($orderBy, $direction)->get();

        } catch (Exception $e) {
            report($e);
            return collect();
        }
    }
public function checkRoomAvailability($roomId, $date, $fromHour, $toHour, $excludeBookingId = null): bool
{
    return $this->getModel()
        ->whereHas('rooms', function ($q) use ($roomId) {
            $q->where('room_id', $roomId);
        })
        ->where('status', BookingStatusEnum::CONFIRMED->value)
        ->where('date', $date)
        ->when($excludeBookingId, function ($q) use ($excludeBookingId) {
            $q->where('id', '!=', $excludeBookingId);
        })
        ->where(function ($q) use ($fromHour, $toHour) {
            $q->whereBetween('from_hour', [$fromHour, $toHour])
                ->orWhereBetween('to_hour', [$fromHour, $toHour])
                ->orWhere(function ($q2) use ($fromHour, $toHour) {
                    $q2->where('from_hour', '<', $fromHour)
                        ->where('to_hour', '>', $toHour);
                });
        })
        ->exists();
}
}
