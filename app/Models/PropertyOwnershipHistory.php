<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyOwnershipHistory extends Model
{
    protected $table = 'property_ownership_history';

    protected $fillable = [
        'allottee_id',
        'property_id',
        'previous_owner_id',
        'new_owner_id',
        'previous_owner_name',
        'previous_owner_cnic',
        'previous_owner_cell',
        'new_owner_name',
        'new_owner_cnic',
        'new_owner_cell',
        'transfer_type',
        'transfer_date',
        'effective_date',
        'transfer_ref_no',
        'transfer_approval_date',
        'possession_handover_date',
        'outstanding_balance_at_transfer',
        'balance_transfer_status',
        'status',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'effective_date' => 'date',
        'transfer_approval_date' => 'date',
        'possession_handover_date' => 'date',
        'outstanding_balance_at_transfer' => 'decimal:2',
    ];

    public function allottee(): BelongsTo
    {
        return $this->belongsTo(Allottee::class, 'allottee_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function previousOwner(): BelongsTo
    {
        return $this->belongsTo(Allottee::class, 'previous_owner_id');
    }

    public function newOwner(): BelongsTo
    {
        return $this->belongsTo(Allottee::class, 'new_owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
