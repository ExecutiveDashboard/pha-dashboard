<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Property extends Model
{
    protected $fillable = [
        'project_id', 'block_no', 'floor', 'flat_no', 'category', 'type',
        'covered_area', 'open_area', 'plot_size', 'maintenance_rate', 'ww_amount',
        'status', 'has_parking', 'has_water', 'parking_charges', 'water_charges'
    ];

    protected $casts = [
        'has_parking'      => 'boolean',
        'has_water'        => 'boolean',
        'covered_area'     => 'integer',
        'open_area'        => 'integer',
        'maintenance_rate' => 'decimal:2',
        'ww_amount'        => 'decimal:2',
        'parking_charges'  => 'decimal:2',
        'water_charges'    => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function owners(): HasMany
    {
        return $this->hasMany(Allottee::class);
    }

    public function activeOwner(): HasOne
    {
        return $this->hasOne(Allottee::class)->where('status', 'active');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(TenantRecord::class);
    }

    public function activeTenant(): HasOne
    {
        return $this->hasOne(TenantRecord::class)->where('is_active', true);
    }

    public function ownershipHistories(): HasMany
    {
        return $this->hasMany(PropertyOwnershipHistory::class, 'property_id');
    }
}
