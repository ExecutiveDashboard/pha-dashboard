<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Allottee;
use App\Models\Bill;
use App\Models\User;
use App\Models\Setting;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BillingLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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

        Setting::setValue('watch_ward_amount', '10000');
        Setting::setValue('delay_charge_percent', '10');
        Setting::setValue('maintenance_rate_per_sqft', '3.07');
        Setting::setValue('watch_ward_cutoff_date', '2023-07-23');
        Setting::setValue('current_billing_month', '2026-07');
        Setting::setValue('billing_admin_override', '0');
    }

    /** @test */
    public function it_records_payment_transactions_and_recalculates_billing_statuses()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@pha.gov.pk'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // 1. Create an allottee
        $allottee = Allottee::create([
            'project_id' => $this->project->id,
            'file_no' => '999-TEST-B',
            'name' => 'Ledger Test Member',
            'cnic' => '99999-9999999-9',
            'cell' => '0300-9999999',
            'category' => 'B',
            'covered_area' => 1000,
            'due_months' => 2,
            'maintenance_charges' => 6140.00,
            'watch_ward_charges' => 0.00,
            'fine' => 0.00,
            'total_maintenance_charges' => 6140.00,
            'amount_paid' => 0.00,
        ]);

        // 2. Setup 2 cumulative bills
        // Bill 1 (Month 1): total 3070
        $bill1 = Bill::create([
            'project_id' => $this->project->id,
            'allottee_id' => $allottee->id,
            'bill_month' => '2026-05',
            'psid' => 'PHAF-999-202605',
            'maintenance_amount' => 3070.00,
            'ww_amount' => 0.00,
            'fine_amount' => 0.00,
            'total_amount' => 3070.00,
            'paid_amount' => 0.00,
            'status' => 'unpaid',
        ]);

        // Bill 2 (Month 2): total 6140 (cumulative)
        $bill2 = Bill::create([
            'project_id' => $this->project->id,
            'allottee_id' => $allottee->id,
            'bill_month' => '2026-06',
            'psid' => 'PHAF-999-202606',
            'maintenance_amount' => 6140.00,
            'ww_amount' => 0.00,
            'fine_amount' => 0.00,
            'total_amount' => 6140.00,
            'paid_amount' => 0.00,
            'status' => 'unpaid',
        ]);

        // 3. Post a payment of 6140 (pays both months because Bill 2 is cumulative)
        $response = $this->actingAs($admin)->post(route('allottees.payment', $allottee), [
            'amount_paid' => 6140.00,
            'payment_mode' => 'cash',
            'payment_date' => '2026-07-11',
            'payment_ref' => 'TXN-101',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 4. Assert that a transaction was created
        $txn = PaymentTransaction::where('allottee_id', $allottee->id)->first();
        $this->assertNotNull($txn);
        $this->assertEquals(6140.00, (float)$txn->amount_paid);

        // 5. Assert that the allottee has amount_paid = 6140
        $allottee->refresh();
        $this->assertEquals(6140.00, (float)$allottee->amount_paid);

        // 6. Assert that both bills are marked paid with correct paid_amounts
        $bill1->refresh();
        $bill2->refresh();
        $this->assertEquals('paid', $bill1->status);
        $this->assertEquals(3070.00, (float)$bill1->paid_amount);
        $this->assertEquals('paid', $bill2->status);
        $this->assertEquals(6140.00, (float)$bill2->paid_amount);

        // 7. Verify Dashboard collections metric matches 6140.00 exactly (no double-counting)
        $responseDashboard = $this->actingAs($admin)->get(route('dashboard', ['fy' => '2026-27']));
        $responseDashboard->assertStatus(200);

        // Access view data
        $totalPaid = $responseDashboard->viewData('totalPaid');
        $this->assertEquals(6140.00, (float)$totalPaid);
    }
}
