<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic settings
        Setting::create([
            'key' => 'area_b',
            'value' => '1496',
            'label' => 'Category B Area (Sq Ft)',
            'type' => 'number',
            'group' => 'billing',
        ]);

        Setting::create([
            'key' => 'area_e',
            'value' => '912',
            'label' => 'Category E Area (Sq Ft)',
            'type' => 'number',
            'group' => 'billing',
        ]);

        Setting::create([
            'key' => 'maintenance_rate_per_sqft',
            'value' => '3.07',
            'label' => 'Maintenance Rate',
            'type' => 'number',
            'group' => 'billing',
        ]);
        
        Setting::create([
            'key' => 'watch_ward_amount',
            'value' => '10000',
            'label' => 'W&W',
            'type' => 'number',
            'group' => 'billing',
        ]);

        Setting::create([
            'key' => 'watch_ward_cutoff_date',
            'value' => '2023-07-23',
            'label' => 'W&W Cutoff',
            'type' => 'text',
            'group' => 'billing',
        ]);

        Setting::create([
            'key' => 'delay_charge_percent',
            'value' => '10',
            'label' => 'Delay Charge Percent',
            'type' => 'number',
            'group' => 'billing',
        ]);
    }

    /** @test */
    public function dashboard_uses_settings_for_category_areas()
    {
        // Update to custom setting values
        Setting::where('key', 'area_b')->update(['value' => '1500']);
        Setting::where('key', 'area_e')->update(['value' => '912']);

        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin_test@pha.gov.pk',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // Check if standard charges table displays the values matching the settings
        $response->assertSee('1,500');
        $response->assertSee('912');
        
        // Assert the calculated values are correct
        // For B: 1500 * 3.07 = 4605
        $response->assertSee('4,605.00');
        // For E: 912 * 3.07 = 2799.84
        $response->assertSee('2,799.84');
    }

    /** @test */
    public function active_project_bank_details_override_global_settings()
    {
        $project = \App\Models\Project::find(1);
        if (!$project) {
            $project = \App\Models\Project::create([
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
                'bank_account_no' => 'PROJECT-BANK-123',
                'bank_name' => 'Project Bank',
                'bank_branch' => 'Project Branch',
            ]);
        } else {
            $project->update([
                'bank_account_no' => 'PROJECT-BANK-123',
                'bank_name' => 'Project Bank',
                'bank_branch' => 'Project Branch',
                'is_active' => true,
            ]);
        }

        $user = User::create([
            'name' => 'Portal Allottee',
            'email' => 'portal_test@pha.gov.pk',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        
        $allottee = \App\Models\Allottee::create([
            'project_id' => 1,
            'name' => 'Allottee 1',
            'bps' => '17',
            'cnic' => '11111-1111111-1',
            'cell_no' => '0300-1234567',
            'category' => 'B',
            'status' => 'active',
            'covered_area' => 1496,
            'amount_paid' => 0,
            'total_maintenance_charges' => 100,
        ]);

        // Mock login session
        session(['portal_allottee_id' => $allottee->id]);

        $response = $this->actingAs($user)->get('/portal/dashboard');
        $response->assertStatus(200);
        $this->assertEquals('PROJECT-BANK-123', $response->viewData('bankAccNo'));
        $this->assertEquals('Project Bank', $response->viewData('bankName'));
        $this->assertEquals('Project Branch', $response->viewData('bankBranch'));
    }
}
