<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffAttendance extends Model
{
    protected static function booted()
    {
        static::addGlobalScope('project', function ($builder) {
            $activeProject = \App\Models\Project::active();
            if ($activeProject) {
                $builder->whereHas('staff', function ($q) use ($activeProject) {
                    $q->where('maintenance_staff.project_id', $activeProject->id);
                });
            }
        });
    }

    protected $table = 'staff_attendance';

    protected $fillable = [
        'maintenance_staff_id',
        'attendance_date',
        'status',
        'remarks',
        'recorded_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function staff(): BelongsTo
    {
        return $this->belongsTo(MaintenanceStaff::class, 'maintenance_staff_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'present'  => 'Present',
            'absent'   => 'Absent',
            'half_day' => 'Half Day',
            'on_leave' => 'On Leave',
            'holiday'  => 'Holiday',
            default    => '—',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'present'  => 'bg-success',
            'absent'   => 'bg-danger',
            'half_day' => 'bg-warning text-dark',
            'on_leave' => 'bg-info text-dark',
            'holiday'  => 'bg-secondary',
            default    => 'bg-light text-dark border',
        };
    }

    /** Returns the numeric weight of a status (for salary calculation) */
    public function getWeightAttribute(): float
    {
        return match ($this->status) {
            'present'  => 1.0,
            'half_day' => 0.5,
            default    => 0.0,
        };
    }
}
