<?php

namespace App\Modules\Auth\Application\UseCases\Admin;




use App\Modules\Auth\Application\DTOS\Admin\LogoutDTO;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Modules\Base\Domain\Holders\EmployeeHolder;
use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Auth\Application\DTOS\Admin\LoginDTO;
use App\Modules\Auth\Application\DTOS\Admin\EmployeeDTO;
use App\Modules\Auth\Application\DTOS\Admin\CheckCodeDTO;
use App\Modules\Auth\Domain\Services\Email\EmailNotification;
use App\Modules\Auth\Application\DTOS\Admin\EmployeeFilterDTO;
use App\Modules\Auth\Http\Enums\EmployeeTypeEnum;
use App\Modules\Auth\Http\Resources\Admin\AdminResource;
use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use App\Modules\Auth\Infrastructure\Persistence\Repositories\Admin\EmployeeRepository;
use App\Modules\Base\Application\Response\DataFailed;
use App\Modules\Base\Application\Response\DataSuccess;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class EmployeeUseCase
{

    protected $employeeRepository;
    /**
     *  @var Employee
     */

    protected $employee;


    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

        public function login(LoginDTO $loginDTO): DataStatus
    {
        try {
            // Log::info('Login attempt for employee', [
            //     'email' => $loginDTO->email,
            //     'phone' => $loginDTO->phone,
            //     'device_token' => $loginDTO->device_token,
            // ]);
            $employee = $this->employeeRepository->login($loginDTO);
            if ($employee) {

                if ($loginDTO->EmployeeDevice() != []) {
                    $employee->EmployeeDevices()->updateOrCreate(['device_token' => $loginDTO->device_token], $loginDTO->EmployeeDevice());
                }
                $employee['api_token'] = $employee->createToken('api_token')->plainTextToken;
                $credentials = $loginDTO->credential();
                $fieldType = checkCredentialType($credentials);
                if ($employee->is_email_verified || $employee->is_phone_verified) {
                    $employee->sendOtp($fieldType, $this->employeeRepository);
                }

            }
            // dd($employee);
            return DataSuccess(
                status: true,
                message: 'success',
                data: new AdminResource($employee)
            );
        } catch (\Exception $e) {
            $statusCode = $e->getCode() && $e->getCode() != 0 && $e->getCode() < 500
                ? $e->getCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR; // or another default

            return new DataFailed(
                statusCode: $statusCode,
                message: $e->getMessage()
            );
        }
    }

    public function logout(LogoutDTO $logoutDTO): DataStatus
    {
        try {
            $response = $this->employeeRepository->logout($logoutDTO);
            return new DataSuccess(
                message: 'success',
            );
        } catch (\Exception $e) {
            $statusCode = $e->getCode() && $e->getCode() != 0 && $e->getCode() < 500
                ? $e->getCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR; // or another default

            return new DataFailed(
                statusCode: $statusCode,
                message: $e->getMessage()
            );
        }
    }

    public function fetchEmployees(EmployeeFilterDTO $EmployeeFilterDTO): DataStatus
    {
        try {
            Log::info('EmployeeFilterDTO', $EmployeeFilterDTO->toArray());
            // dd($EmployeeFilterDTO);
            $Employees = $this->employeeRepository->filter($EmployeeFilterDTO);
            return DataSuccess(
                status: true,
                message: 'success',
                data: AdminResource::collection($Employees)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function fetchEmployeeDetails(EmployeeDTO $EmployeeDTO): DataStatus
    {
        try {
            $Employee = $this->employeeRepository->getById($EmployeeDTO->employee_id);
            return DataSuccess(
                status: true,
                message: 'success',
                data: new AdminResource($Employee)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createEmployee(EmployeeDTO $EmployeeDTO): DataStatus
    {
        try {
            $parent_id = Auth::user()->id;
            $EmployeeDTO->parent_id = $parent_id;
            // dd($parent_id);
            $Employee = $this->employeeRepository->create($EmployeeDTO);
            // dd($Employee);
            return DataSuccess(
                status: true,
                message: 'success',
                data: new AdminResource($Employee)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function updateEmployee(EmployeeDTO $EmployeeDTO): DataStatus
    {
        try {
            $Employee = $this->employeeRepository->getById($EmployeeDTO->employee_id);
            if($Employee->employee_type !== EmployeeTypeEnum::SUPER_ADMIN->value){
                $Employee = $this->employeeRepository->update($EmployeeDTO->employee_id, $EmployeeDTO);
            }

            return DataSuccess(
                status: true,
                message: 'success',
                data: new AdminResource($Employee)
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function deleteEmployee(EmployeeDTO $EmployeeDTO): DataStatus
    {
        try {
            $Employee = $this->employeeRepository->delete($EmployeeDTO->employee_id);
            return DataSuccess(
                status: true,
                message: 'success',
                data: true
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }
}
