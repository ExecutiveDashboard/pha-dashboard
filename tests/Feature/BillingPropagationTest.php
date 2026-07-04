<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Allottee;
use App\Models\Bill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillingPropagationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an active project
        $this->project = Project::create([
            'name' => 'Test Project',
            'full_name' => 'Test Project Islamabad',
            'code' => 'TEST',
            'city' => 'Islamabad',
            'maintenance_rate' => 3.00,
            'ww_amount' => 5000.00,
            'delay_percent' => 10.00,
            'is_active' => true,
        ]);
        
        // Create an admin user for authentication in controller actions if needed
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function it_propagates_payment_to_prior_unpaid_bills_for_category_b()
    {
        // 1. Create a Category B Allottee
        $allottee = Allottee::create([
            'project_id' => $this->project->id,
            'file_no' => '405-TEST-B',
            'name' => 'Cat B Member',
            'cnic' => '11111-1111111-1',
            'cell' => '0300-1111111',
            'category' => 'B',
            'covered_area' => 1000,
            'due_months' => 3,
            'maintenance_charges' => 9000.00,
            'watch_ward_charges' => 0,
            'fine' => 900.00,
            'total_maintenance_charges' => 9900.00,
            'amount_paid' => 0,
        ]);

        // 2. Create multiple unpaid bills
        $bill1 = Bill::create([
            'project_id' => $this->project->id,
            'allottee_id' => $allottee->id,
            'bill_month' => '2026-01',
            'psid' => 'TESTPSID-202601',
            'maintenance_amount' => 3000.00,
            'ww_amount' => 0,
            'fine_amount' => 0,
            'total_amount' => 3000.00,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $bill2 = Bill::create([
            'project_id' => $this->project->id,
            'allottee_id' => $allottee->id,
            'bill_month' => '2026-02',
            'psid' => 'TESTPSID-202602',
            'maintenance_amount' => 6000.00,
            'ww_amount' => 0,
            'fine_amount' => 300.00,
            'total_amount' => 6300.00,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $bill3 = Bill::create([
            'project_id' => $this->project->id,
            'allottee_id' => $allottee->id,
            'bill_month' => '2026-03',
            'psid' => 'TESTPSID-202603',
            'maintenance_amount' => 9000.00,
            'ww_amount' => 0,
            'fine_amount' => 900.00,
            'total_amount' => 9900.00,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        // 3. Post a payment on the latest cumulative bill (bill3)
        $this->actingAs($this->admin);
        $response = $this->post(route('monthly-bills.pay', $bill3->id), [
            'paid_amount' => 9900.00,
            'payment_mode' => 'psid',
            'payment_date' => '2026-06-30',
            'payment_ref' => 'REF-001',
        ]);

        $response->assertStatus(302);

        // 4. Assert that bill3 is paid and locked
        $bill3->refresh();
        $this->assertEquals('paid', $bill3->status);
        $this->assertEquals(9900.00, (float)$bill3->paid_amount);
        $this->assertTrue($bill3->is_locked);

        // 5. Assert that previous bills (bill1 and bill2) have been automatically propagated to PAID
        $bill1->refresh();
        $bill2->refresh();
        $this->assertEquals('paid', $bill1->status);
        $this->assertEquals(3000.00, (float)$bill1->paid_amount);
        $this->assertEquals('paid', $bill2->status);
        $this->assertEquals(6300.00, (float)$bill2->paid_amount);
    }

    /** @test */
    public function it_propagates_payment_to_prior_unpaid_bills_for_category_e()
    {
        // 1. Create a Category E Allottee
        $allottee = Allottee::create([
            'project_id' => $this->project->id,
            'file_no' => '405-TEST-E',
            'name' => 'Cat E Member',
            'cnic' => '22222-2222222-2',
            'cell' => '0300-2222222',
            'category' => 'E',
            'covered_area' => 1200,
            'due_months' => 2,
            'maintenance_charges' => 7200.00,
            'watch_ward_charges' => 0,
            'fine' => 360.00,
            'total_maintenance_charges' => 7560.00,
            'amount_paid' => 0,
        ]);

        // 2. Create multiple unpaid bills
        $bill1 = Bill::create([
            'project_id' => $this->project->id,
            'allottee_id' => $allottee->id,
            'bill_month' => '2026-01',
            'psid' => 'TESTPSIDE-202601',
            'maintenance_amount' => 3600.00,
            'ww_amount' => 0,
            'fine_amount' => 0,
            'total_amount' => 3600.00,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $bill2 = Bill::create([
            'project_id' => $this->project->id,
            'allottee_id' => $allottee->id,
            'bill_month' => '2026-02',
            'psid' => 'TESTPSIDE-202602',
            'maintenance_amount' => 7200.00,
            'ww_amount' => 0,
            'fine_amount' => 360.00,
            'total_amount' => 7560.00,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        // 3. Post a payment on the latest cumulative bill (bill2) for Category E
        $this->actingAs($this->admin);
        $response = $this->post(route('monthly-bills-e.pay', $bill2->id), [
            'paid_amount' => 7560.00,
            'payment_mode' => 'psid',
            'payment_date' => '2026-06-30',
            'payment_ref' => 'REFE-001',
        ]);

        $response->assertStatus(302);

        // 4. Assert that bill2 is paid and locked
        $bill2->refresh();
        $this->assertEquals('paid', $bill2->status);
        $this->assertEquals(7560.00, (float)$bill2->paid_amount);
        $this->assertTrue($bill2->is_locked);

        // 5. Assert that previous bill (bill1) has been automatically propagated to PAID
        $bill1->refresh();
        $this->assertEquals('paid', $bill1->status);
        $this->assertEquals(3600.00, (float)$bill1->paid_amount);
    }

    /** @test */
    public function it_does_not_affect_unrelated_allottees()
    {
        // 1. Create Allottee A (pays) and Allottee B (unrelated)
        $allotteeA = Allottee::create([
            'project_id' => $this->project->id,
            'file_no' => '405-ALL-A',
            'name' => 'Allottee A',
            'cnic' => '33333-3333333-3',
            'cell' => '0300-3333333',
            'category' => 'B',
            'covered_area' => 1000,
            'due_months' => 2,
            'maintenance_charges' => 6000.00,
            'watch_ward_charges' => 0,
            'fine' => 300.00,
            'total_maintenance_charges' => 6300.00,
            'amount_paid' => 0,
        ]);

        $allotteeB = Allottee::create([
            'project_id' => $this->project->id,
            'file_no' => '405-ALL-B',
            'name' => 'Allottee B',
            'cnic' => '44444-4444444-4',
            'cell' => '0300-4444444',
            'category' => 'B',
            'covered_area' => 1000,
            'due_months' => 2,
            'maintenance_charges' => 6000.00,
            'watch_ward_charges' => 0,
            'fine' => 300.00,
            'total_maintenance_charges' => 6300.00,
            'amount_paid' => 0,
        ]);

        // 2. Create bills for both
        $billA1 = Bill::create([
            'project_id' => $this->project->id,
            'allottee_id' => $allotteeA->id,
            'bill_month' => '2026-01',
            'psid' => 'PSID-A1',
            'total_amount' => 3000.00,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);
        $billA2 = Bill::create([
            'project_id' => $this->project->id,
            'allottee_id' => $allotteeA->id,
            'bill_month' => '2026-02',
            'psid' => 'PSID-A2',
            'total_amount' => 6300.00,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $billB1 = Bill::create([
            'project_id' => $this->project->id,
            'allottee_id' => $allotteeB->id,
            'bill_month' => '2026-01',
            'psid' => 'PSID-B1',
            'total_amount' => 3000.00,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);
        $billB2 = Bill::create([
            'project_id' => $this->project->id,
            'allottee_id' => $allotteeB->id,
            'bill_month' => '2026-02',
            'psid' => 'PSID-B2',
            'total_amount' => 6300.00,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        // 3. Post a payment on Allottee A's latest bill
        $this->actingAs($this->admin);
        $this->post(route('monthly-bills.pay', $billA2->id), [
            'paid_amount' => 6300.00,
            'payment_mode' => 'psid',
            'payment_date' => '2026-06-30',
            'payment_ref' => 'REF-A2',
        ]);

        // 4. Assert Allottee A's bills are paid
        $billA1->refresh();
        $billA2->refresh();
        $this->assertEquals('paid', $billA1->status);
        $this->assertEquals('paid', $billA2->status);

        // 5. Assert Allottee B's bills remain completely unpaid
        $billB1->refresh();
        $billB2->refresh();
        $this->assertEquals('unpaid', $billB1->status);
        $this->assertEquals('unpaid', $billB2->status);
    }
}
