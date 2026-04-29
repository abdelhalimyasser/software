<?php

namespace Tests\Unit\Models;

use App\Models\Employee;
use App\Models\Enums\UserRole;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class EmployeeModelTest extends TestCase
{
    use DatabaseMigrations;

    public function test_employee_generates_emp_id_on_creation(): void
    {
        $employee = Employee::create([
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'phone_number' => '0100000000',
            'password' => 'password',
            'role' => UserRole::EMPLOYEE->value,
        ]);

        $this->assertNotNull($employee->emp_id);
    }

    public function test_employee_emp_id_has_correct_format(): void
    {
        $employee = Employee::create([
            'first_name' => 'Format',
            'last_name' => 'Test',
            'email' => 'format@example.com',
            'phone_number' => '0100000001',
            'password' => 'password',
            'role' => UserRole::EMPLOYEE->value,
        ]);

        $this->assertMatchesRegularExpression(
            '/^NH-EMP-\d{4}-\d{4}$/',
            $employee->emp_id
        );
    }

    public function test_employee_emp_id_contains_current_year(): void
    {
        $employee = Employee::create([
            'first_name' => 'Year',
            'last_name' => 'Test',
            'email' => 'year@example.com',
            'phone_number' => '0100000002',
            'password' => 'password',
            'role' => UserRole::EMPLOYEE->value,
        ]);

        $this->assertStringContainsString(date('Y'), $employee->emp_id);
    }

    public function test_employee_emp_ids_are_unique(): void
    {
        $ids = [];
        for ($i = 0; $i < 5; $i++) {
            $employee = Employee::create([
                'first_name' => "Emp$i",
                'last_name' => 'Test',
                'email' => "emp$i@example.com",
                'phone_number' => "010000000$i",
                'password' => 'password',
                'role' => UserRole::EMPLOYEE->value,
            ]);
            $ids[] = $employee->emp_id;
        }

        $this->assertCount(5, array_unique($ids));
    }

    public function test_employee_does_not_overwrite_existing_emp_id_on_update(): void
    {
        $employee = Employee::create([
            'first_name' => 'Keep',
            'last_name' => 'Id',
            'email' => 'keep@example.com',
            'phone_number' => '0100000006',
            'password' => 'password',
            'role' => UserRole::EMPLOYEE->value,
        ]);

        $originalEmpId = $employee->emp_id;

        $employee->first_name = 'Updated';
        $employee->save();

        $this->assertSame($originalEmpId, $employee->fresh()->emp_id);
    }

    public function test_employee_fillable_includes_parent_fields(): void
    {
        $employee = new Employee();
        $fillable = $employee->getFillable();

        $this->assertContains('first_name', $fillable);
        $this->assertContains('last_name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('emp_id', $fillable);
    }
}
