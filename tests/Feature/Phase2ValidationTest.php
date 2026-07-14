<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Allottee;
use App\Models\Bill;
use App\Models\User;
use App\Models\Setting;
use App\Models\MaintenanceStaff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class Phase2ValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Active Project (I-16/3)
        $this->project = Project::find(1);
        if (!$this->project) {
            $this->project = Project::create([
                'id' => 1,
                'name' => 'I-16/3 Islamabad',
                'full_name' => 'PHAF I-16/3 Islamabad',
                'code' => 'PHAF-I16',
                'city' => 'Islamabad',
                'maintenance_rate' => 3.07,
                'ww_amount' => 10000.00,
                'ww_cutoff_date' => '2023-07-23',
                'delay_percent' => 10.00,
                'is_active' => true,
            ]);
        } else {
            $this->project->update([
                'name' => 'I-16/3 Islamabad',
                'full_name' => 'PHAF I-16/3 Islamabad',
                'code' => 'PHAF-I16',
                'city' => 'Islamabad',
                'maintenance_rate' => 3.07,
                'ww_amount' => 10000.00,
                'ww_cutoff_date' => '2023-07-23',
                'delay_percent' => 10.00,
                'is_active' => true,
            ]);
        }

        // 2. Setup general billing settings in database
        Setting::setValue('watch_ward_amount', '10000');
        Setting::setValue('delay_charge_percent', '10');
        Setting::setValue('maintenance_rate_per_sqft', '3.07');
        Setting::setValue('watch_ward_cutoff_date', '2023-07-23');
        Setting::setValue('current_billing_month', '2026-07');
        Setting::setValue('billing_admin_override', '0');
    }

    /** @test */
    public function rbac_authorizes_correct_roles_on_staff_attendance_and_complaints()
    {
        // Define users with various roles
        $roles = [
            'super_admin'            => 200,
            'admin'                  => 200,
            'maintenance_supervisor' => 200,
            'data_entry'             => 200,
            'viewer'                 => 403,
            'whatsapp_sender'        => 403,
        ];

        foreach ($roles as $role => $expectedStatus) {
            $user = User::create([
                'name' => 'User ' . $role,
                'email' => $role . '_rbac@pha.gov.pk',
                'password' => bcrypt('password'),
                'role' => $role,
            ]);

            // Test Staff Attendance index endpoint
            $response = $this->actingAs($user)->get(route('admin.staff.attendance.index'));
            $response->assertStatus($expectedStatus);

            // Test CMS index endpoint
            $response = $this->actingAs($user)->get(route('admin.complaints.index'));
            // Note: index action uses getBaseQuery which lets maintenance_staff view their own, but others can access
            if ($role === 'whatsapp_sender') {
                // If it's a role not permitted in general layout
                // Let's verify that viewers can view complaints but whatsapp_senders get 403 on reports/categories
                $response = $this->actingAs($user)->get(route('admin.complaints.categories.index'));
                $response->assertStatus(403);
            }
        }
    }

    /** @test */
    public function it_excludes_accumulated_fines_from_compounding_calculations()
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pha.gov.pk',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        // Set standard area setting to 1000 for Category B during test
        \App\Models\Setting::setValue('area_b', '1000');

        // Create an allottee with an existing fine of 500
        $allottee = Allottee::create([
            'project_id' => $this->project->id,
            'file_no' => '405-TEST-B',
            'name' => 'Compounding Test Member',
            'cnic' => '11111-1111111-1',
            'cell' => '0300-1111111',
            'category' => 'B',
            'covered_area' => 1000, // 1000 sqft * 3.07 rate = 3070 base monthly rent
            'due_months' => 1,
            'possession_date' => '2023-01-01', // Possession before cutoff means no new W&W month dues
            'maintenance_charges' => 3070.00,
            'watch_ward_charges' => 0.00,
            'fine' => 500.00, // Existing fine
            'total_maintenance_charges' => 3570.00,
            'amount_paid' => 0.00,
        ]);



        // Generate next month's bill (Month 2)
        // Arrears = base rent (3070) + W&W (0) - paid (0) = 3070.
        // Fines must be 10% of 3070 = 307.00.
        // With compound fine logic fixed, it should NOT calculate 10% of (3070 + 500) = 357.00.
        $response = $this->actingAs($superAdmin)->post(route('monthly-bills.generate'), [
            'month' => '2026-07',
        ]);

        if (session('error')) {
            dump("Billing Generate Error (Fines Test): " . session('error'));
        }

        $response->assertSessionHasNoErrors();

        // Retrieve generated bill
        $bill = Bill::where('allottee_id', $allottee->id)->where('bill_month', '2026-07')->first();
        $this->assertNotNull($bill);

        // Expected fine = old_fine (500) + new_fine (307) = 807
        $this->assertEquals(807.00, (float)$bill->fine_amount);
    }

    /** @test */
    public function it_calculates_watch_and_ward_charges_dynamically_from_settings()
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pha.gov.pk',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        // Cutoff: 2023-07-23.
        // Possession date: 2023-10-23 (3 months after cutoff).
        $allottee = Allottee::create([
            'project_id' => $this->project->id,
            'file_no' => '406-TEST-B',
            'name' => 'Dynamic Cutoff Member',
            'cnic' => '22222-2222222-2',
            'cell' => '0300-2222222',
            'category' => 'B',
            'covered_area' => 1000,
            'due_months' => 0,
            'possession_date' => '2023-10-23',
            'maintenance_charges' => 0.00,
            'watch_ward_charges' => 0.00,
            'fine' => 0.00,
            'total_maintenance_charges' => 0.00,
            'amount_paid' => 0.00,
        ]);

        // Dynamic check 1: Run with default project cutoff (2023-07-23) -> 3 months = 30,000 W&W
        $response1 = $this->actingAs($superAdmin)->post(route('monthly-bills.generate'), ['month' => '2026-07']);
        if (session('error')) {
            dump("Billing Generate Error (W&W Test 1): " . session('error'));
        }
        $bill1 = Bill::where('allottee_id', $allottee->id)->where('bill_month', '2026-07')->first();
        $this->assertNotNull($bill1);
        $this->assertEquals(30000.00, (float)$bill1->ww_amount);

        // Delete bill and update project cutoff to '2023-09-23' (1 month after cutoff) -> 1 month = 10,000 W&W
        $bill1->delete();
        $this->project->update(['ww_cutoff_date' => '2023-09-23']);

        $response2 = $this->actingAs($superAdmin)->post(route('monthly-bills.generate'), ['month' => '2026-07']);
        if (session('error')) {
            dump("Billing Generate Error (W&W Test 2): " . session('error'));
        }
        $bill2 = Bill::where('allottee_id', $allottee->id)->where('bill_month', '2026-07')->first();
        $this->assertNotNull($bill2);
        $this->assertEquals(10000.00, (float)$bill2->ww_amount);
    }

    /** @test */
    public function it_handles_payroll_with_zero_working_days_without_division_by_zero()
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@pha.gov.pk',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        // Create a staff member
        $staff = MaintenanceStaff::create([
            'project_id' => $this->project->id,
            'name' => 'Salaried Employee',
            'designation' => 'Electrician',
            'salary_type' => 'monthly',
            'basic_salary' => 30000.00,
            'allowances' => 2000.00,
        ]);

        // Test route action parameters
        // Generate payroll for a month where total days == holidays (meaning working days = 0)
        // Assert that calculation logic handles it without DivisionByZero crash
        $response = $this->actingAs($superAdmin)->post(route('admin.staff.payroll.generate'), [
            'payroll_month' => '2026-07',
            'total_days' => 31,
            'holidays' => 31, // 0 working days
        ]);

        if (session('error')) {
            dump("Payroll Generate Error: " . session('error'));
        }

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
