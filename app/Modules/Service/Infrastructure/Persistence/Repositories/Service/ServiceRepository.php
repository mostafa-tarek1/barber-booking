<?php

namespace App\Modules\Service\Infrastructure\Persistence\Repositories\Service;


use Exception;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Modules\Base\Domain\DTO\BaseDTOInterface;
use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository extends BaseRepositoryAbstract
{
    public function __construct()
    {
        $this->setModel(new Service());
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

            // فلترة بالكلمة (search)
            $query->when(isset($dto->word), function ($q) use ($dto, $translatableFields, $operator) {
                $q->where(function ($subQuery) use ($dto, $translatableFields, $operator) {
                    foreach ($translatableFields as $field) {
                        $subQuery->orWhereTranslationLike($field, "%{$dto->word}%");
                    }
                });
            });

            // باقي الفلاتر من الـ DTO
            foreach ($dto->toArray() as $key => $value) {
                if (filled($value) && !in_array($key, ['word', 'orderBy', 'direction'])) {
                    $query
                        ->when(in_array($key, $translatableFields), fn($q) => $q->whereTranslationLike($key, "%{$value}%"))
                        ->when(is_array($value), fn($q) => $q->whereIn($key, $value))
                        ->when(is_bool($value), fn($q) => $q->where($key, $value))
                        ->when(is_numeric($value), fn($q) => $q->where($key, $operator, $value))
                        ->when(is_string($value) && !in_array($key, $translatableFields), fn($q) => $q->where($key, $operator, ($operator === 'like' ? "%{$value}%" : $value)));
                }
            }

            // ترتيب آمن
            $orderBy = in_array($dto->orderBy, ['id', 'price', 'duration', 'created_at']) ? $dto->orderBy : 'id';
            $direction = in_array(strtolower($dto->direction), ['asc', 'desc']) ? $dto->direction : 'asc';

            return $paginate
                ? $query->orderBy($orderBy, $direction)->paginate($limit)
                : $query->orderBy($orderBy, $direction)->get();

        } catch (Exception $e) {
            report($e);
            return collect();
        }
    }
}
