<?php

namespace Tests\Feature;

use App\Modules\Auth\Infrastructure\Persistence\Models\Admin\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_employee_can_change_own_password(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'employee')
            ->postJson('/dashboard/change_password', [
                'current_password' => 'OldPassword123',
                'password' => 'NewPassword456',
                'password_confirmation' => 'NewPassword456',
            ])
            ->assertSuccessful();

        $this->assertTrue(Hash::check('NewPassword456', $admin->fresh()->password));
    }

    public function test_wrong_current_password_does_not_change_password(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'employee')
            ->postJson('/dashboard/change_password', [
                'current_password' => 'WrongPassword123',
                'password' => 'NewPassword456',
                'password_confirmation' => 'NewPassword456',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');

        $this->assertTrue(Hash::check('OldPassword123', $admin->fresh()->password));
    }

    public function test_cashier_cannot_change_password_from_management_settings(): void
    {
        $cashier = Employee::query()->create([
            'first_name' => 'كاشير',
            'password' => 'OldPassword123',
            'employee_type' => 'employee',
            'status' => 'active',
        ]);

        $this->actingAs($cashier, 'employee')
            ->postJson('/dashboard/change_password', [
                'current_password' => 'OldPassword123',
                'password' => 'NewPassword456',
                'password_confirmation' => 'NewPassword456',
            ])
            ->assertForbidden();

        $this->assertTrue(Hash::check('OldPassword123', $cashier->fresh()->password));
    }

    private function admin(): Employee
    {
        return Employee::query()->create([
            'first_name' => 'مدير',
            'email' => 'manager@example.com',
            'password' => 'OldPassword123',
            'employee_type' => 'admin',
            'status' => 'active',
        ]);
    }
}
