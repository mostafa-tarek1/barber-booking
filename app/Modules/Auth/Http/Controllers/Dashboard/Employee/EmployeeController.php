<?php

namespace App\Modules\Auth\Http\Controllers\Dashboard\Employee;

use App\Modules\Auth\Application\UseCases\Admin\EmployeeUseCase;
use App\Modules\Auth\Http\Requests\Api\Admin\LoginRequest;
use App\Modules\Auth\Http\Requests\Api\Admin\LogoutRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Admin\CreateEmployeeRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Admin\FetchEmployeeRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Admin\UpdateEmployeeRequest;
use App\Modules\Auth\Http\Requests\Global\Employee\EmployeeIdRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmployeeController extends BaseController
{
    protected $employeeUseCase;

    public function __construct(EmployeeUseCase $employeeUseCase)
    {
        $this->employeeUseCase = $employeeUseCase;
    }

    public function login(LoginRequest $request)
    {
        return $this->employeeUseCase->login($request->toDTO())->response();
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'كلمة المرور الحالية غير صحيحة.',
            ]);
        }

        $request->user()->update(['password' => $validated['password']]);

        return response()->json([
            'status' => 200,
            'message' => 'تم تغيير كلمة المرور بنجاح.',
            'data' => true,
        ]);
    }

    public function logout(LogoutRequest $request)
    {
        return $this->employeeUseCase->logout($request->toDTO())->response();
    }

    public function fetchEmployees(FetchEmployeeRequest $request)
    {
        $filters = $request->toDTO();
        if (! in_array($request->user()->employee_type, ['admin', 'super_admin'], true)) {
            $filters->employee_type = 'barber';
            $filters->status = 'active';
        }

        return $this->employeeUseCase->fetchEmployees($filters)->response();
    }

    public function fetchEmployeeDetails(EmployeeIdRequest $request)
    {
        return $this->employeeUseCase->fetchEmployeeDetails($request->toDTO())->response();
    }

    public function createEmployee(CreateEmployeeRequest $request)
    {
        return $this->employeeUseCase->createEmployee($request->toDTO())->response();
    }

    public function updateEmployee(UpdateEmployeeRequest $request)
    {
        // dd($request->toDTO());
        Log::info('Update Employee Request', [
            'request' => $request->toDTO(),
        ]);

        return $this->employeeUseCase->updateEmployee($request->toDTO())->response();
    }

    public function deleteEmployee(EmployeeIdRequest $request)
    {
        return $this->employeeUseCase->deleteEmployee($request->toDTO())->response();
    }
}
