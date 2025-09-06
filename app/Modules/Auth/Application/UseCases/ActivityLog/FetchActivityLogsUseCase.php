<?php
namespace App\Modules\Auth\Application\UseCases\ActivityLog;

use App\Modules\Auth\Application\DTOS\ActivityLog\ActivityLogFilterDTO;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\ActivityLog\ActivityLogRepositoryInterface;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Domain\Holders\AuthHolder;
use Spatie\Activitylog\Models\Activity;

class FetchActivityLogsUseCase
{
    protected $employee;

    public function __construct(private ActivityLogRepositoryInterface $repository) {
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }
    public function handle(ActivityLogFilterDTO $dto)
    {$logs = Activity::query()
    ->with('causer')
    ->latest()
    ->get();
        return $this->repository->fetch($dto);
    }

}
