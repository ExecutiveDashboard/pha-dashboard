<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

        static::saving(function ($allottee) {
            $allottee->overdue_months = $allottee->calculateOverdueMonths();

            // Validate that one property can have only one active/current owner at a time
            if ($allottee->status === 'active' && $allottee->property_id) {
                $exists = static::withoutGlobalScopes()
                    ->where('property_id', $allottee->property_id)
                    ->where('status', 'active')
                    ->where('id', '!=', $allottee->id)
                    ->exists();

                if ($exists) {
                    throw new \InvalidArgumentException("A property can have only one active/current owner at a time.");
                }
            }
        });
    }

    protected $fillable = [
        'project_id', 'property_id',
        'file_no','membership_no','fg','endorsed_files','loan_mortgage',
        'handed_over','temporary_occupancy','possession_date','booking_transfer_date',
        'gp','block_no','floor','flat_no','bps','cnic','balloting_fcfs','pal',
        'transfer','verification','scanning','name','office_name','cadre_group',
        'date_of_joining','post_held','dos','dob','office_address','mailing_address',
        'office_tel','home_tel','cell','category','covered_area','due_months','overdue_months',
        'maintenance_charges','watch_ward_charges','fine','total_maintenance_charges','city',
        'amount_paid','payment_mode','payment_date','payment_ref',
        'ww_charged','ww_charged_date',
        'ownership_start_date', 'ownership_end_date', 'transfer_type', 'transfer_ref_no', 'status', 'remarks', 'occupancy_status',
        'father_spouse_name', 'email'
    ];

    protected $casts = [
        'possession_date' => 'date',
        'booking_transfer_date' => 'date',
        'date_of_joining' => 'date',
        'dos' => 'date',
        'dob' => 'date',
        'payment_date' => 'date',
        'due_months' => 'integer',
        'overdue_months' => 'integer',
        'maintenance_charges' => 'decimal:2',
        'watch_ward_charges' => 'decimal:2',
        'fine' => 'decimal:2',
        'total_maintenance_charges' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'covered_area'    => 'integer',
        'ww_charged'      => 'boolean',
        'ww_charged_date' => 'date',
        'ownership_start_date' => 'date',
        'ownership_end_date' => 'date',
    ];

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class)->orderByDesc('bill_month');
    }

    public function isDefaulter(): bool
    {
        $threshold = (int) Setting::getValue('defaulter_months_threshold', 3);
        return $this->overdue_months >= $threshold;
    }

    public function calculateOverdueMonths(): int
    {
        $amountPaid = (float) $this->amount_paid;
        $bills = \App\Models\Bill::withoutGlobalScopes()
            ->where('allottee_id', $this->id)
            ->whereNotIn('status', ['settled'])
            ->orderBy('bill_month', 'asc')
            ->get();

        $overdueMonthsCount = 0;
        $previousMaint = 0;

        $rate = 3.07;
        $activeProject = \App\Models\Project::withoutGlobalScopes()->find($this->project_id);
        if ($activeProject) {
            $rate = $activeProject->maintenance_rate;
        }
        $parkingRate = $this->has_parking ? ($this->parking_charges > 0 ? $this->parking_charges : (float) \App\Models\Setting::getValue('parking_charges_rate', 500)) : 0;
        $waterRate = $this->has_water ? ($this->water_charges > 0 ? $this->water_charges : (float) \App\Models\Setting::getValue('water_charges_rate', 1000)) : 0;
        $monthlyBase = ($rate * $this->covered_area) + $parkingRate + $waterRate;

        foreach ($bills as $bill) {
            $incrementalMaint = (float)$bill->maintenance_amount - $previousMaint;
            $billMonths = $monthlyBase > 0 ? max(1, (int) round($incrementalMaint / $monthlyBase)) : 1;
            
            $cumulativeCharges = (float)$bill->maintenance_amount + (float)$bill->ww_amount + (float)$bill->fine_amount;
            
            $isPaid = ($bill->status === 'paid' || $bill->status === 'settled' || $amountPaid >= ($cumulativeCharges - \App\Models\Bill::ROUNDING_TOLERANCE));
            
            if (!$isPaid) {
                $overdueMonthsCount += $billMonths;
            }
            
            $previousMaint = (float)$bill->maintenance_amount;
        }

        if ($this->due_months > 0 && $overdueMonthsCount > $this->due_months) {
            $overdueMonthsCount = $this->due_months;
        }

        return $overdueMonthsCount;
    }

    public function getAmountPendingAttribute(): float
    {
        return max(0, (float)$this->total_maintenance_charges - (float)$this->amount_paid);
    }

    public function getPaymentStatusAttribute(): string
    {
        if ((float)$this->amount_paid >= ((float)$this->total_maintenance_charges - \App\Models\Bill::ROUNDING_TOLERANCE)) {
            return 'paid';
        }
        if ((float)$this->amount_paid > 0) {
            return 'partial';
        }
        return 'unpaid';
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

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function recalculateBillingStatuses(): void
    {
        // 1. Sum all transaction records to get true amount_paid
        $totalPaid = (float) $this->transactions()->sum('amount_paid');
        
        $this->amount_paid = $totalPaid;
        $this->save(); // Triggers the saving event which recalculates overdue_months

        // 2. Fetch all bills of the allottee ordered chronologically
        $bills = $this->bills()->withoutGlobalScopes()->orderBy('bill_month', 'asc')->get();

        foreach ($bills as $bill) {
            $billPaid = min((float)$bill->total_amount, $totalPaid);
            
            $status = 'unpaid';
            if ($billPaid >= ((float)$bill->total_amount - \App\Models\Bill::ROUNDING_TOLERANCE)) {
                $status = 'paid';
            } elseif ($billPaid > 0) {
                $status = 'partial';
            }

            $bill->update([
                'paid_amount' => $billPaid,
                'status'      => $status,
                'is_locked'   => $status === 'paid',
                'locked_at'   => $status === 'paid' ? ($bill->locked_at ?? now()) : null,
            ]);
        }
    }
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(TenantRecord::class);
    }

    public function activeTenant()
    {
        return $this->tenants()->where('is_active', true)->first();
    }

    public function ownershipHistories(): HasMany
    {
        return $this->hasMany(PropertyOwnershipHistory::class, 'allottee_id');
    }

    public function getBlockNoAttribute($value)
    {
        return $this->property ? $this->property->block_no : $value;
    }

    public function getFlatNoAttribute($value)
    {
        return $this->property ? $this->property->flat_no : $value;
    }

    public function getFloorAttribute($value)
    {
        return $this->property ? $this->property->floor : $value;
    }

    public function getCategoryAttribute($value)
    {
        return $this->property ? $this->property->category : $value;
    }

    public function getCoveredAreaAttribute($value)
    {
        return $this->property ? $this->property->covered_area : $value;
    }

    public function getHasParkingAttribute($value)
    {
        return $this->property ? $this->property->has_parking : $value;
    }

    public function getHasWaterAttribute($value)
    {
        return $this->property ? $this->property->has_water : $value;
    }

    public function getParkingChargesAttribute($value)
    {
        return $this->property ? $this->property->parking_charges : $value;
    }

    public function getWaterChargesAttribute($value)
    {
        return $this->property ? $this->property->water_charges : $value;
    }

    public function getOccupancyStatusAttribute($value)
    {
        if ($value === 'owner' || $value === 'owner_occupied') {
            return 'owner_occupied';
        }
        if ($value === 'tenant' || $value === 'tenant_occupied') {
            return 'tenant_occupied';
        }
        return 'owner_occupied';
    }
}
