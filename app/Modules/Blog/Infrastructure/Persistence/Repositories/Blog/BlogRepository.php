<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Repositories\Blog;


use Exception;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Modules\Base\Domain\DTO\BaseDTOInterface;
use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Blog\Infrastructure\Persistence\Models\Blog\Blog;
use Illuminate\Database\Eloquent\Collection;

class BlogRepository extends BaseRepositoryAbstract
{
    public function __construct()
    {
        $this->setModel(new Blog());
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
                $dto->lat, $dto->lng, $dto->lat
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

    if ($paginate) {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            $limit,
            request('page', 1),
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    return $this->getModel()->newCollection();
}

}

}
