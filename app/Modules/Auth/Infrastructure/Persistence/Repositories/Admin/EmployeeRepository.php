<?php

namespace App\Modules\Auth\Infrastructure\Persistence\Repositories\Admin;

use App\Modules\Auth\Application\DTOS\Admin\LoginDTO;
use App\Modules\Auth\Http\Enums\EmployeeTypeEnum;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Modules\Base\Domain\Repositories\BaseRepositoryAbstract;
use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Base\Domain\DTO\BaseDTOInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeRepository extends BaseRepositoryAbstract
{
  /**
   * @var Employee
   */
  protected $employee;
  protected $whatsAppApiService;

  public function __construct()
  {
    $this->setModel(new Employee());
    $this->employee = Auth::user();
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
      $query = $this->getModel()->query()
        ->whereNot('employee_type', EmployeeTypeEnum::SUPER_ADMIN->value);

      $query->when(isset($dto->word), function ($q) use ($dto) {
        $q->where(function ($q2) use ($dto) {
          $q2->where('first_name', 'like', "%{$dto->word}%")
            ->orWhere('email', 'like', "%{$dto->word}%")
            ->orWhere('phone', 'like', "%{$dto->word}%");
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


      // dd($query->get());


      return $paginate ? $query->orderBy($dto->orderBy, $dto->direction)->paginate($limit) : $query->orderBy($dto->orderBy, $dto->direction)->get();
    } catch (Exception $e) {
      report($e);
      return collect();
    }
  }

  public function login(LoginDTO $loginDTO): Employee
  {
    try {
      $credentials = $loginDTO->toArray();
      $fieldType = array_key_first($credentials);
      $result = $this->getWhere($fieldType, $credentials[$fieldType], 'first');
      $employee = $result instanceof Employee ? $result : null;
      // dd($employee);
      Log::info(['employee' => $employee]);
      if (!$employee || !Hash::check($loginDTO->password, $employee->password)) {
        throw new \Exception('The provided credentials are incorrect.', Response::HTTP_BAD_REQUEST);
      }
      return $employee;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage(), Response::HTTP_BAD_REQUEST);
    }
  }


  public function checkCredential($checkCredentialDTO): Employee
  {
    try {
      $credentials = $checkCredentialDTO->credential();
      $fieldType = array_key_first($credentials);
      // dd($fieldType);
      // dd($credentials[$fieldType]);
      $employee = $this->getWhere($fieldType, $credentials[$fieldType], 'first');
      if (!$employee) {
        throw new \Exception('The provided credentials are incorrect.');
      }

      return $employee;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function logout($logoutDTO): bool
  {
    try {
      // resolve currently authenticated employee at call time (Auth::user may be null at repo construction)
      $employee = $this->employee ?? Auth::user();
      if (!$employee) {
        throw new \Exception('Unauthenticated user', Response::HTTP_UNAUTHORIZED);
      }

      // relation is defined as employeeDevices()
      $employee_device = $employee->employeeDevices()->where('device_token', $logoutDTO->deviceToken)->first();
      if ($employee_device) {
        $employee_device->delete();
      }

      // delete sanctum tokens for this employee
      if (method_exists($employee, 'tokens')) {
        $employee->tokens()->delete();
      }

      return true;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }


  public function sendWhatsAppMessage($phone, $message)
  {
    try {
      $handeledPhone = handelPhone($phone);
      $response = $this->whatsAppApiService->sendMessage($handeledPhone['phone'], $message, 'generalauth', $handeledPhone['countryCode']);
      return $response;
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

}
