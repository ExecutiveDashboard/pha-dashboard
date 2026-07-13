<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Project;
use App\Models\Allottee;
use App\Models\Property;
use App\Models\TenantRecord;
use App\Models\User;
use App\Models\Setting;
use App\Models\Complaint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class TenantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed default project
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

        // 4. Create current active allottee record
        $this->allottee = Allottee::create([
            'project_id'           => 1,
            'property_id'          => $this->property->id,
            'name'                 => 'Original Owner',
            'cnic'                 => '11111-1111111-1',
            'cell'                 => '0300-1111111',
            'file_no'              => 'PHA-A-101',
            'membership_no'        => 'M-A101',
            'ownership_start_date' => now(),
            'status'               => 'active',
            'occupancy_status'     => 'owner_occupied',
            'city'                 => 'Islamabad',
        ]);

        // 5. Create super_admin user
        $this->admin = User::firstOrCreate(
            ['email' => 'admin@pha.gov.pk'],
            [
                'name'     => 'Super Admin',
                'password' => bcrypt('password'),
                'role'     => 'super_admin',
            ]
        );
    }

    /** @test */
    public function it_toggles_tenant_occupied_and_saves_tenant_record()
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('allottees.update', $this->allottee), [
            'name' => 'Original Owner',
            'cnic' => '11111-1111111-1',
            'cell' => '0300-1111111',
            'block_no' => 'A',
            'flat_no' => '101',
            'category' => 'B',
            'occupancy_status' => 'tenant_occupied',
            'tenant_name' => 'First Tenant',
            'tenant_cnic' => '22222-2222222-2',
            'mobile_no' => '0333-2222222',
            'agreement_no' => 'TA-001',
            'agreement_start_date' => '2026-07-01',
            'agreement_expiry_date' => '2027-06-30',
        ]);

        $response->assertRedirect(route('allottees.show', $this->allottee));
        
        // Assert property and owner statuses updated
        $this->property->refresh();
        $this->allottee->refresh();
        $this->assertEquals('tenant_occupied', $this->allottee->occupancy_status);

        // Assert tenant record inserted
        $tenant = TenantRecord::where('property_id', $this->property->id)->where('is_active', true)->first();
        $this->assertNotNull($tenant);
        $this->assertEquals('First Tenant', $tenant->tenant_name);
        $this->assertEquals('22222-2222222-2', $tenant->tenant_cnic);
    }

    /** @test */
    public function it_deactivates_tenant_when_switching_back_to_owner_occupied()
    {
        $this->actingAs($this->admin);

        // First, add a tenant
        $tenant = TenantRecord::create([
            'project_id' => 1,
            'allottee_id' => $this->allottee->id,
            'property_id' => $this->property->id,
            'tenant_name' => 'Temporary Tenant',
            'tenant_cnic' => '33333-3333333-3',
            'mobile_no' => '0300-3333333',
            'agreement_no' => 'TA-002',
            'agreement_start_date' => now()->toDateString(),
            'agreement_expiry_date' => now()->addYear()->toDateString(),
            'occupancy_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        $this->allottee->update(['occupancy_status' => 'tenant_occupied']);

        // Update occupancy back to owner_occupied
        $response = $this->put(route('allottees.update', $this->allottee), [
            'name' => 'Original Owner',
            'cnic' => '11111-1111111-1',
            'cell' => '0300-1111111',
            'block_no' => 'A',
            'flat_no' => '101',
            'category' => 'B',
            'occupancy_status' => 'owner_occupied',
        ]);

        $response->assertRedirect(route('allottees.show', $this->allottee));

        // Assert tenant was deactivated
        $tenant->refresh();
        $this->assertFalse($tenant->is_active);
    }

    /** @test */
    public function it_replaces_active_tenant_and_archives_previous_record()
    {
        $this->actingAs($this->admin);

        // 1. Add active tenant A
        $tenantA = TenantRecord::create([
            'project_id' => 1,
            'allottee_id' => $this->allottee->id,
            'property_id' => $this->property->id,
            'tenant_name' => 'Tenant A',
            'tenant_cnic' => '44444-4444444-4',
            'mobile_no' => '0300-4444444',
            'agreement_no' => 'TA-004',
            'agreement_start_date' => '2026-01-01',
            'agreement_expiry_date' => '2026-12-31',
            'occupancy_date' => '2026-01-01',
            'is_active' => true,
        ]);

        $this->allottee->update(['occupancy_status' => 'tenant_occupied']);

        // 2. Put update request with different tenant details (Tenant B)
        $response = $this->put(route('allottees.update', $this->allottee), [
            'name' => 'Original Owner',
            'cnic' => '11111-1111111-1',
            'cell' => '0300-1111111',
            'block_no' => 'A',
            'flat_no' => '101',
            'category' => 'B',
            'occupancy_status' => 'tenant_occupied',
            'tenant_name' => 'Tenant B',
            'tenant_cnic' => '55555-5555555-5',
            'mobile_no' => '0300-5555555',
            'agreement_no' => 'TA-005',
            'agreement_start_date' => '2027-01-01',
            'agreement_expiry_date' => '2027-12-31',
        ]);

        // 3. Assert Tenant A is deactivated and Tenant B is created and active
        $tenantA->refresh();
        $this->assertFalse($tenantA->is_active);

        $tenantB = TenantRecord::where('property_id', $this->property->id)->where('is_active', true)->first();
        $this->assertNotNull($tenantB);
        $this->assertEquals('Tenant B', $tenantB->tenant_name);
        $this->assertEquals('55555-5555555-5', $tenantB->tenant_cnic);
    }

    /** @test */
    public function test_tc_tenant_bill_001()
    {
        $this->actingAs($this->admin);

        // 1. Arrange: setup a separate property and allottee matching the scenario
        $property = Property::create([
            'project_id'       => 1,
            'block_no'         => 'B',
            'floor'            => 'First Floor',
            'flat_no'          => '202',
            'category'         => 'E',
            'type'             => 'apartment',
            'covered_area'     => 1200,
            'maintenance_rate' => 3.07,
            'ww_amount'        => 10000.00,
            'status'           => 'Allotted',
        ]);

        $allottee = Allottee::create([
            'id' => 409,
            'project_id' => 1,
            'property_id' => $property->id,
            'name' => 'Scenario Owner',
            'cnic' => '61101-5973880-1',
            'cell' => '0300-9168952',
            'file_no' => 'PHA-A-409',
            'ownership_start_date' => now(),
            'status' => 'active',
            'occupancy_status' => 'tenant_occupied',
            'city' => 'Islamabad',
        ]);

        $tenant = TenantRecord::create([
            'project_id' => 1,
            'allottee_id' => $allottee->id,
            'property_id' => $property->id,
            'tenant_name' => 'Active Tenant Name',
            'tenant_cnic' => '33333-3333333-3',
            'mobile_no' => '0300-9999999',
            'agreement_no' => 'AGR-409',
            'agreement_start_date' => '2026-07-01',
            'agreement_expiry_date' => '2027-06-30',
            'occupancy_date' => '2026-07-01',
            'is_active' => true,
        ]);

        // 2. Act: open edit page and verify occupancy
        $editResponse = $this->get(route('allottees.edit', $allottee));
        $editResponse->assertStatus(200);
        $editResponse->assertSee('tenant_occupied');
        $editResponse->assertSee('Active Tenant Name');

        // 3. Act: Open View Bill
        $billResponse = $this->get(route('bills.show', $allottee));
        $billResponse->assertStatus(200);
        
        // Assert View Bill contains occupancy and tenant info
        $billResponse->assertSee('Tenant Occupied');
        $billResponse->assertSee('Active Tenant Name');
        $billResponse->assertSee('33333-3333333-3');
        $billResponse->assertSee('0300-9999999');

        // 4. Act: Generate PDF Bill
        $pdfResponse = $this->get(route('bills.pdf', $allottee));
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function it_searches_allottees_by_tenant_fields_and_flat_number()
    {
        $this->actingAs($this->admin);

        // 1. Setup a tenant property
        $property = Property::create([
            'project_id'       => 1,
            'block_no'         => 'C',
            'floor'            => 'Second Floor',
            'flat_no'          => '303',
            'category'         => 'B',
            'type'             => 'apartment',
            'covered_area'     => 1000,
            'maintenance_rate' => 3.07,
            'ww_amount'        => 10000.00,
            'status'           => 'Allotted',
        ]);

        $allottee = Allottee::create([
            'project_id'           => 1,
            'property_id'          => $property->id,
            'name'                 => 'Search Owner Name',
            'cnic'                 => '12345-1234567-1',
            'cell'                 => '0300-1234567',
            'file_no'              => 'PHA-A-303',
            'ownership_start_date' => now(),
            'status'               => 'active',
            'occupancy_status'     => 'tenant_occupied',
            'city'                 => 'Islamabad',
        ]);

        $tenant = TenantRecord::create([
            'project_id' => 1,
            'allottee_id' => $allottee->id,
            'property_id' => $property->id,
            'tenant_name' => 'John Doe Tenant',
            'tenant_cnic' => '77777-7777777-7',
            'mobile_no' => '0333-7777777',
            'agreement_no' => 'TA-303',
            'agreement_start_date' => now()->toDateString(),
            'agreement_expiry_date' => now()->addYear()->toDateString(),
            'occupancy_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        // Search by tenant name
        $response = $this->get(route('allottees.index', ['search' => 'John Doe']));
        $response->assertStatus(200);
        $response->assertSee('John Doe Tenant');
        $response->assertSee('Search Owner Name');

        // Search by tenant CNIC
        $response = $this->get(route('allottees.index', ['search' => '77777-7777777-7']));
        $response->assertStatus(200);
        $response->assertSee('John Doe Tenant');

        // Search by flat number
        $response = $this->get(route('allottees.index', ['search' => '303']));
        $response->assertStatus(200);
        $response->assertSee('Search Owner Name');
    }

    /** @test */
    public function it_searches_bills_by_tenant_fields_and_flat_number()
    {
        $this->actingAs($this->admin);

        // Setup a tenant property
        $property = Property::create([
            'project_id'       => 1,
            'block_no'         => 'D',
            'floor'            => 'Third Floor',
            'flat_no'          => '404',
            'category'         => 'B',
            'type'             => 'apartment',
            'covered_area'     => 1000,
            'maintenance_rate' => 3.07,
            'ww_amount'        => 10000.00,
            'status'           => 'Allotted',
        ]);

        $allottee = Allottee::create([
            'project_id'           => 1,
            'property_id'          => $property->id,
            'name'                 => 'Bill Owner Name',
            'cnic'                 => '12345-1234567-2',
            'cell'                 => '0300-1234568',
            'file_no'              => 'PHA-A-404',
            'ownership_start_date' => now(),
            'status'               => 'active',
            'occupancy_status'     => 'tenant_occupied',
            'city'                 => 'Islamabad',
        ]);

        $tenant = TenantRecord::create([
            'project_id' => 1,
            'allottee_id' => $allottee->id,
            'property_id' => $property->id,
            'tenant_name' => 'Jane Smith Tenant',
            'tenant_cnic' => '88888-8888888-8',
            'mobile_no' => '0333-8888888',
            'agreement_no' => 'TA-404',
            'agreement_start_date' => now()->toDateString(),
            'agreement_expiry_date' => now()->addYear()->toDateString(),
            'occupancy_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        // Search by tenant name
        $response = $this->get(route('bills.search', ['q' => 'Jane Smith']));
        $response->assertStatus(200);
        $response->assertSee('Bill Owner Name');

        // Search by flat number
        $response = $this->get(route('bills.search', ['q' => '404']));
        $response->assertStatus(200);
        $response->assertSee('Bill Owner Name');
    }

    /** @test */
    public function it_shows_tenant_info_on_complaint_details_page()
    {
        $this->actingAs($this->admin);

        // Setup tenant property
        $property = Property::create([
            'project_id'       => 1,
            'block_no'         => 'E',
            'floor'            => 'Fourth Floor',
            'flat_no'          => '505',
            'category'         => 'B',
            'type'             => 'apartment',
            'covered_area'     => 1000,
            'maintenance_rate' => 3.07,
            'ww_amount'        => 10000.00,
            'status'           => 'Allotted',
        ]);

        $allottee = Allottee::create([
            'project_id'           => 1,
            'property_id'          => $property->id,
            'name'                 => 'Complaint Owner Name',
            'cnic'                 => '12345-1234567-3',
            'cell'                 => '0300-1234569',
            'file_no'              => 'PHA-A-505',
            'ownership_start_date' => now(),
            'status'               => 'active',
            'occupancy_status'     => 'tenant_occupied',
            'city'                 => 'Islamabad',
        ]);

        $tenant = TenantRecord::create([
            'project_id' => 1,
            'allottee_id' => $allottee->id,
            'property_id' => $property->id,
            'tenant_name' => 'Complaint Tenant Name',
            'tenant_cnic' => '99999-9999999-9',
            'mobile_no' => '0333-9999999',
            'agreement_no' => 'TA-505',
            'agreement_start_date' => now()->toDateString(),
            'agreement_expiry_date' => now()->addYear()->toDateString(),
            'occupancy_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        // Create category
        $category = \App\Models\ComplaintCategory::create([
            'project_id' => 1,
            'name' => 'Plumbing',
            'code' => 'PLB',
            'is_active' => true,
        ]);

        // Create a complaint
        $complaint = Complaint::create([
            'project_id' => 1,
            'allottee_id' => $allottee->id,
            'category_id' => $category->id,
            'subject' => 'Water Leaking',
            'description' => 'Water is leaking in bathroom',
            'priority' => 'high',
            'status' => 'new',
        ]);

        // Access complaint details page
        $response = $this->get(route('admin.complaints.show', $complaint));
        $response->assertStatus(200);
        
        // Assert it sees tenant details and occupancy status
        $response->assertSee('Tenant Occupied');
        $response->assertSee('Complaint Tenant Name');
        $response->assertSee('0333-9999999');
    }
}
