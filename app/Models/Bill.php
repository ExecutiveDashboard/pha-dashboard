<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Bill extends Model
{
    protected static function booted()
    {
        static::addGlobalScope('project', function ($builder) {
            $activeProject = \App\Models\Project::active();
            if ($activeProject) {
                $builder->where('bills.project_id', $activeProject->id);
            }
        });
    }

    protected $fillable = [
        'project_id', 'allottee_id', 'bill_month', 'psid',
        'maintenance_amount', 'ww_amount', 'fine_amount', 'total_amount',
        'paid_amount', 'status', 'payment_mode', 'payment_date', 'payment_ref',
        'settled_by', 'settled_note', 'is_locked', 'locked_at',
    ];

    protected $casts = [
        'maintenance_amount' => 'decimal:2',
        'ww_amount'          => 'decimal:2',
        'fine_amount'        => 'decimal:2',
        'total_amount'       => 'decimal:2',
        'paid_amount'        => 'decimal:2',
        'payment_date'       => 'date',
        'locked_at'          => 'datetime',
        'is_locked'          => 'boolean',
    ];

    public function allottee(): BelongsTo
    {
        return $this->belongsTo(Allottee::class);
    }

    /** Human-readable bill month e.g. "May 2025" */
    public function getBillMonthLabelAttribute(): string
    {
        return Carbon::createFromFormat('Y-m', $this->bill_month)->format('F Y');
    }

    /** Amount still outstanding */
    public function getAmountDueAttribute(): float
    {
        return max(0, (float)$this->total_amount - (float)$this->paid_amount);
    }

    /** Generate a PSID string for a given allottee and month */
    public static function generatePsid(Allottee $allottee, string $month): string
    {
        $block = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $allottee->block_no ?? 'X'));
        $flat  = str_pad(preg_replace('/[^0-9]/', '', $allottee->flat_no ?? '0'), 3, '0', STR_PAD_LEFT);
        $mon   = str_replace('-', '', $month); // 202505
        return 'PHAF-' . $block . $flat . '-' . $mon;
    }

    /** Status badge color helper */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'paid'     => 'success',
            'partial'  => 'warning',
            'settled'  => 'info',
            'locked'   => 'secondary',
            default    => 'danger',
        };
    }
}
