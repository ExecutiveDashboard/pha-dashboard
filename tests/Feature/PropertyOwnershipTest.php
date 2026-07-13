<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Allottee;
use App\Models\Property;
use App\Models\Bill;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PropertyOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed project
        $this->project = Project::create([
            'id' => 1,
            'name' => 'PHA Apartments I-16/3 Islamabad',
            'full_name' => 'PHAF I-16/3 Islamabad',
            'code' => 'PHAF-I16',
            'city' => 'Islamabad',
            'maintenance_rate' => 3.07,
            'ww_amount' => 10000.00,
            'ww_cutoff_date' => '2023-07-23',
            'delay_percent' => 10.00,
            'is_active' => true,
            'is_enabled' => true,
        ]);

        // 2. Setup active project settings
        Setting::setValue('watch_ward_amount', '10000');
        Setting::setValue('delay_charge_percent', '10');
        Setting::setValue('maintenance_rate_per_sqft', '3.07');
        Setting::setValue('watch_ward_cutoff_date', '2023-07-23');
        Setting::setValue('current_billing_month', '2026-07');
        Setting::setValue('billing_admin_override', '0');

        // 3. Create active property
        $this->property = Property::create([
            'project_id'       => 1,
            'block_no'         => 'A',
            'floor'            => 'Ground Floor',
            'flat_no'          => '101',
            'category'         => 'B',
            'type'             => 'apartment',
            'covered_area'     => 1000,
            'maintenance_rate' => 3.07,
            'ww_amount'        => 10000.00,
            'status'           => 'Allotted',
        ]);

        // 4. Create active owner
        $this->allottee = Allottee::create([
            'project_id'           => 1,
            'property_id'          => $this->property->id,
            'name'                 => 'Seller Owner',
            'cnic'                 => '11111-1111111-1',
            'cell'                 => '0300-1111111',
            'file_no'              => 'PHA-A-101',
            'membership_no'        => 'M-A101',
            'ownership_start_date' => '2025-01-01',
            'status'               => 'active',
            'occupancy_status'     => 'owner_occupied',
            'due_months'           => 3,
            'maintenance_charges'  => 9210.00,
            'total_maintenance_charges' => 9210.00,
            'amount_paid'          => 0.00,
            'city'                 => 'Islamabad',
        ]);

        // 5. Add an unpaid bill to the seller
        $this->unpaidBill = Bill::create([
            'allottee_id'        => $this->allottee->id,
            'bill_month'         => '2026-06',
            'maintenance_amount' => 3070.00,
            'total_amount'       => 3070.00,
            'paid_amount'        => 0.00,
            'status'             => 'unpaid',
        ]);

        // 6. Create admin user
        $this->admin = User::firstOrCreate(
            ['email' => 'admin@pha.gov.pk'],
            [
                'name'     => 'Super Admin',
                'password' => bcrypt('password'),
                'role'     => 'super_admin',
            ]
        );

        // Disable CSRF verification for testing
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    /** @test */
    public function it_transfers_property_and_transfers_unpaid_bills_by_default()
    {
        $this->actingAs($this->admin);

        // Ensure outstanding balance transfer is enabled
        Setting::setValue('transfer_outstanding_balance', '1');

        $response = $this->post(route('allottees.transfer', $this->allottee), [
            'new_owner_name' => 'Buyer Owner',
            'new_owner_father_spouse' => 'Spouse Name',
            'new_owner_cnic' => '99999-9999999-9',
            'new_owner_cell' => '0300-9999999',
            'new_owner_email' => 'buyer@pha.gov.pk',
            'transfer_type' => 'sale',
            'transfer_date' => '2026-07-11',
            'effective_date' => '2026-07-11',
            'transfer_approval_date' => '2026-07-10',
            'possession_handover_date' => '2026-07-12',
            'transfer_ref_no' => 'TR-999',
            'remarks' => 'Property sold.',
        ]);

        // Seller should now be inactive
        $this->allottee->refresh();
        $this->assertEquals('inactive', $this->allottee->status);
        $this->assertEquals(0, $this->allottee->due_months);
        $this->assertEquals(0, $this->allottee->total_maintenance_charges);

        // Buyer should be created and active
        $buyer = Allottee::where('property_id', $this->property->id)->where('status', 'active')->first();
        $this->assertNotNull($buyer);
        $this->assertEquals('Buyer Owner', $buyer->name);
        $this->assertEquals('Spouse Name', $buyer->father_spouse_name);
        $this->assertEquals('buyer@pha.gov.pk', $buyer->email);
        $this->assertEquals('99999-9999999-9', $buyer->cnic);
        $this->assertEquals('active', $buyer->status);

        // Due months and outstanding balance should carry over to buyer
        $this->assertEquals(3, $buyer->due_months);
        $this->assertEquals(9210.00, $buyer->total_maintenance_charges);

        // Bill should be reassigned to buyer
        $this->unpaidBill->refresh();
        $this->assertEquals($buyer->id, $this->unpaidBill->allottee_id);

        // Verify PropertyOwnershipHistory record is created
        $history = \App\Models\PropertyOwnershipHistory::where('property_id', $this->property->id)->first();
        $this->assertNotNull($history);
        $this->assertEquals($this->allottee->id, $history->previous_owner_id);
        $this->assertEquals($buyer->id, $history->new_owner_id);
        $this->assertEquals('sale', $history->transfer_type);
        $this->assertEquals('2026-07-10', $history->transfer_approval_date->format('Y-m-d'));
        $this->assertEquals('2026-07-12', $history->possession_handover_date->format('Y-m-d'));
        $this->assertEquals(9210.00, $history->outstanding_balance_at_transfer);
        $this->assertEquals('transferred', $history->balance_transfer_status);

        $response->assertRedirect(route('allottees.show', $buyer));
    }

    /** @test */
    public function it_transfers_property_but_leaves_bills_with_seller_if_configured()
    {
        $this->actingAs($this->admin);

        // Disable outstanding balance transfer
        Setting::setValue('transfer_outstanding_balance', '0');

        $response = $this->post(route('allottees.transfer', $this->allottee), [
            'new_owner_name' => 'Buyer Owner 2',
            'new_owner_father_spouse' => 'Spouse Name 2',
            'new_owner_cnic' => '88888-8888888-8',
            'new_owner_cell' => '0300-8888888',
            'new_owner_email' => 'buyer2@pha.gov.pk',
            'transfer_type' => 'sale',
            'transfer_date' => '2026-07-11',
            'effective_date' => '2026-07-11',
            'transfer_approval_date' => '2026-07-10',
            'possession_handover_date' => '2026-07-12',
            'transfer_ref_no' => 'TR-888',
            'remarks' => 'Property sold without balance transfer.',
        ]);

        // Seller should now be inactive but KEEP their dues and bills
        $this->allottee->refresh();
        $this->assertEquals('inactive', $this->allottee->status);
        $this->assertEquals(3, $this->allottee->due_months);
        $this->assertEquals(9210.00, $this->allottee->total_maintenance_charges);

        // Buyer should be created with 0 balance
        $buyer = Allottee::where('property_id', $this->property->id)->where('status', 'active')->first();
        $this->assertNotNull($buyer);
        $this->assertEquals('Buyer Owner 2', $buyer->name);
        $this->assertEquals(0, $buyer->due_months);
        $this->assertEquals(0, $buyer->total_maintenance_charges);

        // Bill should NOT be reassigned
        $this->unpaidBill->refresh();
        $this->assertEquals($this->allottee->id, $this->unpaidBill->allottee_id);

        // Verify PropertyOwnershipHistory record shows retained status
        $history = \App\Models\PropertyOwnershipHistory::where('property_id', $this->property->id)->first();
        $this->assertNotNull($history);
        $this->assertEquals('retained', $history->balance_transfer_status);

        $response->assertRedirect(route('allottees.show', $buyer));
    }

    /** @test */
    public function current_owner_is_editable_but_historical_owner_is_read_only()
    {
        $this->actingAs($this->admin);

        // Update active owner - should succeed
        $response = $this->put(route('allottees.update', $this->allottee), [
            'name' => 'Updated Seller Owner',
            'father_spouse_name' => 'New Father Name',
            'cnic' => '11111-1111111-1',
            'email' => 'seller@pha.gov.pk',
            'occupancy_status' => 'owner_occupied',
            'block_no' => 'A',
            'flat_no' => '101',
            'floor' => 'Ground Floor',
            'category' => 'B',
        ]);
        $response->assertRedirect();
        $this->allottee->refresh();
        $this->assertEquals('Updated Seller Owner', $this->allottee->name);
        $this->assertEquals('New Father Name', $this->allottee->father_spouse_name);
        $this->assertEquals('seller@pha.gov.pk', $this->allottee->email);

        // Mark owner as inactive
        $this->allottee->update(['status' => 'inactive']);

        // Try updating inactive owner - should fail and redirect to show page with error message
        $response = $this->put(route('allottees.update', $this->allottee), [
            'name' => 'Attempted Edit Name',
            'father_spouse_name' => 'Attempted Edit Father',
            'cnic' => '11111-1111111-1',
            'occupancy_status' => 'owner_occupied',
            'block_no' => 'A',
            'flat_no' => '101',
            'floor' => 'Ground Floor',
            'category' => 'B',
        ]);
        $response->assertRedirect(route('allottees.show', $this->allottee));
        $this->allottee->refresh();
        $this->assertEquals('Updated Seller Owner', $this->allottee->name);
    }

    /** @test */
    public function cannot_save_multiple_active_owners_for_same_property()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("A property can have only one active/current owner at a time.");

        Allottee::create([
            'project_id' => $this->allottee->project_id,
            'property_id' => $this->allottee->property_id,
            'name' => 'Second Active Owner',
            'cnic' => '22222-2222222-2',
            'status' => 'active',
        ]);
    }
}
