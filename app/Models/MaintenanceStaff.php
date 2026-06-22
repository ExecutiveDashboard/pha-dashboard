<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceStaff extends Model
{
    protected $table = 'maintenance_staff';

    protected $fillable = ['user_id', 'name', 'designation', 'phone', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_staff_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
