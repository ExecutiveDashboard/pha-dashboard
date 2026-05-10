<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Allottee extends Model
{
    protected static function booted()
    {
        static::addGlobalScope('project', function ($builder) {
            $activeProject = \App\Models\Project::active();
            if ($activeProject) {
                $builder->where('allottees.project_id', $activeProject->id);
            }
        });
    }

    protected $fillable = [
        'project_id',
        'file_no','membership_no','fg','endorsed_files','loan_mortgage',
        'handed_over','temporary_occupancy','possession_date','booking_transfer_date',
        'gp','block_no','floor','flat_no','bps','cnic','balloting_fcfs','pal',
        'transfer','verification','scanning','name','office_name','cadre_group',
        'date_of_joining','post_held','dos','dob','office_address','mailing_address',
        'office_tel','home_tel','cell','category','covered_area','due_months',
        'maintenance_charges','watch_ward_charges','fine','total_maintenance_charges','city',
        'amount_paid','payment_mode','payment_date','payment_ref',
        'ww_charged','ww_charged_date',
    ];

    protected $casts = [
        'possession_date' => 'date',
        'booking_transfer_date' => 'date',
        'date_of_joining' => 'date',
        'dos' => 'date',
        'dob' => 'date',
        'payment_date' => 'date',
        'due_months' => 'integer',
        'maintenance_charges' => 'decimal:2',
        'watch_ward_charges' => 'decimal:2',
        'fine' => 'decimal:2',
        'total_maintenance_charges' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'covered_area'    => 'integer',
        'ww_charged'      => 'boolean',
        'ww_charged_date' => 'date',
    ];

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class)->orderByDesc('bill_month');
    }

    public function isDefaulter(): bool
    {
        $threshold = (int) Setting::getValue('defaulter_months_threshold', 3);
        return $this->due_months >= $threshold;
    }

    public function getAmountPendingAttribute(): float
    {
        return max(0, (float)$this->total_maintenance_charges - (float)$this->amount_paid);
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->amount_paid <= 0) return 'unpaid';
        if ($this->amount_paid >= $this->total_maintenance_charges) return 'paid';
        return 'partial';
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->name ?? 'N/A';
        if (str_contains($name, ',')) {
            $parts = explode(',', $name);
            return trim($parts[0]) . ' & Others';
        }
        return $name;
    }

    public function getDisplayCnicAttribute(): string
    {
        $cnic = $this->cnic ?? '—';
        if (str_contains($cnic, ',')) {
            $parts = explode(',', $cnic);
            return trim($parts[0]) . ' & Others';
        }
        return $cnic;
    }
}
