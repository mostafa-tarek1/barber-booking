<?php

namespace App\Modules\Base\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $employee = $request->user();

        abort_unless(
            $employee && in_array($employee->employee_type, $roles, true),
            403,
            'ليس لديك صلاحية لتنفيذ هذه العملية.'
        );

        return $next($request);
    }
}
