<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantRecord extends Model
{
    protected $table = 'tenant_records';

    protected $fillable = [
        'allottee_id', 'project_id', 'property_id', 'tenant_name', 'tenant_cnic',
        'spouse_name', 'mobile_no', 'alternate_contact_no', 'agreement_no',
        'agreement_start_date', 'agreement_expiry_date', 'duration_of_stay',
        'occupancy_date', 'is_active', 'remarks', 'tenant_email',
        'permanent_address', 'current_address', 'agreement_date',
        'monthly_rent', 'security_deposit', 'emergency_contact_name',
        'emergency_contact_phone'
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'agreement_start_date'   => 'date',
        'agreement_expiry_date'  => 'date',
        'agreement_date'         => 'date',
        'occupancy_date'         => 'date',
        'monthly_rent'           => 'decimal:2',
        'security_deposit'       => 'decimal:2',
    ];

    public function allottee(): BelongsTo
    {
        return $this->belongsTo(Allottee::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
