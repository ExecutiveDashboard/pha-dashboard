<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceStaff extends Model
{
    protected static function booted()
    {
        static::addGlobalScope('project', function ($builder) {
            $activeProject = \App\Models\Project::active();
            if ($activeProject) {
                $builder->where('maintenance_staff.project_id', $activeProject->id);
            }
        });
    }

    protected $table = 'maintenance_staff';

    protected $fillable = [
        'project_id', 'user_id', 'name', 'designation', 'phone', 'is_active',
        // HR fields
        'cnic', 'joining_date', 'shift', 'salary_type',
        'basic_salary', 'daily_rate', 'allowances',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'joining_date'  => 'date',
        'basic_salary'  => 'decimal:2',
        'daily_rate'    => 'decimal:2',
        'allowances'    => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'assigned_staff_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(StaffAttendance::class, 'maintenance_staff_id');
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(StaffPayroll::class, 'maintenance_staff_id')->orderByDesc('payroll_month');
    }

    /** Payroll record for a specific month (Y-m-d first-of-month format) */
    public function payrollForMonth(string $monthFirstDay): ?StaffPayroll
    {
        return $this->payrolls()->whereDate('payroll_month', $monthFirstDay)->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
