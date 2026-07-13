<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Project;
use App\Models\MaintenanceStaff;
use App\Models\StaffAttendance;
use App\Models\StaffPayroll;
use App\Models\ComplaintCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup two projects
        $this->projectA = Project::find(1);
        if ($this->projectA) {
            $this->projectA->update(['is_active' => true]);
        } else {
            $this->projectA = Project::create([
                'id' => 1,
                'name' => 'Project A',
                'full_name' => 'PHA Project A',
                'code' => 'PROJ-A',
                'city' => 'Islamabad',
                'is_active' => true,
            ]);
        }

        $this->projectB = Project::find(2);
        if ($this->projectB) {
            $this->projectB->update(['is_active' => false]);
        } else {
            $this->projectB = Project::create([
                'id' => 2,
                'name' => 'Project B',
                'full_name' => 'PHA Project B',
                'code' => 'PROJ-B',
                'city' => 'Lahore',
                'is_active' => false,
            ]);
        }
    }

    /** @test */
    public function it_isolates_staff_attendance_payroll_and_complaint_categories_by_active_project()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@pha.gov.pk'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // 1. Create records for Project A (currently active)
        $staffA = MaintenanceStaff::create([
            'project_id' => $this->projectA->id,
            'name' => 'Project A Electrician',
            'designation' => 'Electrician',
            'salary_type' => 'monthly',
            'basic_salary' => 30000.00,
        ]);

        $attendanceA = StaffAttendance::create([
            'maintenance_staff_id' => $staffA->id,
            'attendance_date' => '2026-07-11',
            'status' => 'present',
        ]);

        $payrollA = StaffPayroll::create([
            'maintenance_staff_id' => $staffA->id,
            'payroll_month' => '2026-07-01',
            'salary_type' => 'monthly',
            'total_days' => 31,
            'present_days' => 30,
            'absent_days' => 1,
            'gross_salary' => 30000.00,
            'net_salary' => 29000.00,
            'payment_status' => 'paid',
        ]);

        $categoryA = ComplaintCategory::create([
            'project_id' => $this->projectA->id,
            'name' => 'Project A Carpentry',
            'is_active' => true,
        ]);

        // 2. Temporarily switch active project to Project B to create its records
        $this->projectA->update(['is_active' => false]);
        $this->projectB->update(['is_active' => true]);

        // Clear cached instances in active project check helper if any
        Project::active(); // refresh cache

        $staffB = MaintenanceStaff::create([
            'project_id' => $this->projectB->id,
            'name' => 'Project B Electrician',
            'designation' => 'Electrician',
            'salary_type' => 'monthly',
            'basic_salary' => 35000.00,
        ]);

        $attendanceB = StaffAttendance::create([
            'maintenance_staff_id' => $staffB->id,
            'attendance_date' => '2026-07-11',
            'status' => 'present',
        ]);

        $payrollB = StaffPayroll::create([
            'maintenance_staff_id' => $staffB->id,
            'payroll_month' => '2026-07-01',
            'salary_type' => 'monthly',
            'total_days' => 31,
            'present_days' => 31,
            'absent_days' => 0,
            'gross_salary' => 35000.00,
            'net_salary' => 35000.00,
            'payment_status' => 'paid',
        ]);

        $categoryB = ComplaintCategory::create([
            'project_id' => $this->projectB->id,
            'name' => 'Project B Carpentry',
            'is_active' => true,
        ]);

        // 3. Verify Project B isolation
        $this->assertEquals(1, MaintenanceStaff::count());
        $this->assertEquals($staffB->id, MaintenanceStaff::first()->id);

        $this->assertEquals(1, StaffAttendance::count());
        $this->assertEquals($attendanceB->id, StaffAttendance::first()->id);

        $this->assertEquals(1, StaffPayroll::count());
        $this->assertEquals($payrollB->id, StaffPayroll::first()->id);

        $this->assertEquals(1, ComplaintCategory::count());
        $this->assertEquals($categoryB->id, ComplaintCategory::first()->id);

        // 4. Verify Project A isolation (switch back)
        $this->projectB->update(['is_active' => false]);
        $this->projectA->update(['is_active' => true]);
        Project::active(); // refresh cache

        $this->assertEquals(1, MaintenanceStaff::count());
        $this->assertEquals($staffA->id, MaintenanceStaff::first()->id);

        $this->assertEquals(1, StaffAttendance::count());
        $this->assertEquals($attendanceA->id, StaffAttendance::first()->id);

        $this->assertEquals(1, StaffPayroll::count());
        $this->assertEquals($payrollA->id, StaffPayroll::first()->id);

        $this->assertEquals(1, ComplaintCategory::count());
        $this->assertEquals($categoryA->id, ComplaintCategory::first()->id);
    }
}
