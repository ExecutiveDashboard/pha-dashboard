<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Allottee extends Model
{
    protected $fillable = [
        'file_no','membership_no','fg','endorsed_files','loan_mortgage',
        'handed_over','temporary_occupancy','possession_date','booking_transfer_date',
        'gp','block_no','floor','flat_no','bps','cnic','balloting_fcfs','pal',
        'transfer','verification','scanning','name','office_name','cadre_group',
        'date_of_joining','post_held','dos','dob','office_address','mailing_address',
        'office_tel','home_tel','cell','category','covered_area','due_months',
        'maintenance_charges','watch_ward_charges','fine','total_maintenance_charges','city',
        'amount_paid','payment_mode','payment_date','payment_ref',
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
        'covered_area' => 'integer',
    ];

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
}
