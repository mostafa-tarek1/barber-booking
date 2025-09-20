<?php

namespace App\Modules\Blog\Infrastructure\Persistence\Repositories\Category;


use Exception;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Modules\Base\Domain\DTO\BaseDTOInterface;
use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Blog\Infrastructure\Persistence\Models\Category\Category;
use App\Modules\Order\Application\Enums\Order\OrderDurationTypeEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepositoryAbstract
{
    public function __construct()
    {
        $this->setModel(new Category());
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
            $query->when(isset($dto->is_parent) && $dto->is_parent == true, function ($q){
                $q->whereNull('parent_id');
            })
            ->when(isset($dto->is_parent) && $dto->is_parent === false, function ($q) use ($dto){
                $q->whereNotNull('parent_id');
            });
            // dd($dto);
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
                        ->when(is_numeric($value), fn($q) => $q->where($key, $operator, $value))
                        ->when(is_string($value) && !in_array($key, $translatableFields), fn($q) => $q->where($key, $operator, ($operator === 'like' ? "%{$value}%" : $value)));
                }
            }

            // Apply whereHas relations with whereIn
            foreach ($whereHasRelations as $relation => $conditions) {
                // dd($relation, $conditions);
                $query->whereHas($relation, function ($q) use ($conditions, $relation) {
                    // dd($conditions);
                    foreach ($conditions as $key => $values) {
                        // dd($key, $values);
                        if (is_array($values)) {
                            // dd($key,$values);
                            $q->whereIn($key, $values); // Use whereIn for arrays
                            // dd($q->get());
                        } else {
                            $q->where($key, $values); // Use where for single values
                        }
                    }
                });
            }
            // dd($query->get());

            // Apply whereHasMultiple relations with whereIn
            foreach ($whereHasMultipleRelations as $relationsGroup) {
                foreach ($relationsGroup as $relation => $conditions) {
                    $query->whereHas($relation, function ($q) use ($conditions) {
                        foreach ($conditions as $key => $values) {
                            if (is_array($values)) {
                                $q->whereIn($key, $values); // Use whereIn for arrays
                            } else {
                                $q->where($key, $values); // Use where for single values
                            }
                        }
                    });
                }
            }
            // Order by nearest location if lat & lng exist
            $query->when(isset($dto->lat) && isset($dto->lng), function ($query) use ($dto) {
                $query->select('*', DB::raw("(
                    6371 * acos(
                        cos(radians(?)) *
                        cos(radians(lat)) *
                        cos(radians(lng) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(lat))
                    )
                ) AS distance", [$dto->lat, $dto->lng, $dto->lat]))
                    ->orderBy('distance', 'asc');
            })->when(isset($dto->lat) && isset($dto->lng) && isset($dto->distance), function ($query) use ($dto) {
                $query->select('*', DB::raw("(
                    6371 * acos(
                        cos(radians(?)) *
                        cos(radians(lat)) *
                        cos(radians(lng) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(lat))
                    )
                ) AS distance", [$dto->lat, $dto->lng, $dto->lat]))
                    ->having('distance', '<=', $dto->distance)
                    ->orderBy('distance', 'asc');
            });

            return $paginate ? $query->orderBy($dto->orderBy, $dto->direction)->paginate($limit) : $query->orderBy($dto->orderBy, $dto->direction)->get();
        } catch (Exception $e) {
            report($e);
            return collect();
        }
    }
}
