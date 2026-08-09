<?php

namespace App\Modules\Service\Infrastructure\Persistence\Repositories\Service;


use App\Modules\Booking\Domain\Enums\InvoiceStatusEnum;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Modules\Base\Domain\DTO\BaseDTOInterface;
use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Service\Infrastructure\Persistence\Models\Service\Service;
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
        $query = $this->getModel()
            ->query()
            ->withCount([
                'bookings as bookings_count' => fn ($bookingQuery) => $bookingQuery->where('status', 3),
            ])
            ->withSum([
                'invoiceItems as sales_count' => fn ($invoiceItemQuery) => $invoiceItemQuery
                    ->whereHas('invoice', fn ($invoiceQuery) => $invoiceQuery
                        ->where('status', InvoiceStatusEnum::PAID->value)),
            ], 'quantity');

        $query->when(isset($dto->word), function ($q) use ($dto, $translatableFields) {
            $q->where(function ($subQuery) use ($dto, $translatableFields) {
                foreach ($translatableFields as $field) {
                    $subQuery->orWhereTranslationLike($field, "%{$dto->word}%");
                }
            });
        });

        foreach ($dto->toArray() as $key => $value) {
            if (filled($value) && ! in_array($key, ['word', 'orderBy', 'direction'])) {
                $query
                    ->when(in_array($key, $translatableFields), fn ($q) => $q->whereTranslationLike($key, "%{$value}%"))
                    ->when(is_array($value), fn ($q) => $q->whereIn($key, $value))
                    ->when(is_bool($value), fn ($q) => $q->where($key, $value))
                    ->when(is_numeric($value), fn ($q) => $q->where($key, $operator, $value))
                    ->when(is_string($value) && ! in_array($key, $translatableFields), fn ($q) => $q->where($key, $operator, ($operator === 'like' ? "%{$value}%" : $value)));
            }
        }

        $orderBy = in_array($dto->orderBy, ['id', 'price', 'duration', 'created_at']) ? $dto->orderBy : 'id';
        $direction = in_array(strtolower($dto->direction), ['asc', 'desc']) ? $dto->direction : 'asc';

        return $paginate
            ? $query->orderBy($orderBy, $direction)->paginate($limit)
            : $query->orderBy($orderBy, $direction)->get();
    }
}
