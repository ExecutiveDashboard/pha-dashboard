<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPayroll extends Model
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

    protected $table = 'staff_payroll';

    protected $fillable = [
        'maintenance_staff_id',
        'payroll_month',
        'salary_type',
        'total_days',
        'present_days',
        'absent_days',
        'half_days',
        'leave_days',
        'holiday_days',
        'basic_salary_snapshot',
        'allowances_snapshot',
        'gross_salary',
        'deductions',
        'net_salary',
        'payment_status',
        'payment_date',
        'payment_remarks',
        'generated_by',
    ];

    protected $casts = [
        'payroll_month'          => 'date',
        'payment_date'           => 'date',
        'basic_salary_snapshot'  => 'decimal:2',
        'allowances_snapshot'    => 'decimal:2',
        'gross_salary'           => 'decimal:2',
        'deductions'             => 'decimal:2',
        'net_salary'             => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────────────

    public function staff(): BelongsTo
    {
        return $this->belongsTo(MaintenanceStaff::class, 'maintenance_staff_id');
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getMonthLabelAttribute(): string
    {
        return Carbon::parse($this->payroll_month)->format('F Y');
    }
}
