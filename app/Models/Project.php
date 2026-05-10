<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name', 'full_name', 'code', 'city',
        'maintenance_rate', 'ww_amount', 'ww_cutoff_date', 'delay_percent',
        'bank_account_no', 'bank_name', 'bank_branch',
        'total_units', 'is_active', 'description',
    ];

    protected $casts = [
        'maintenance_rate' => 'decimal:2',
        'ww_amount'        => 'decimal:2',
        'delay_percent'    => 'decimal:2',
        'ww_cutoff_date'   => 'date',
        'is_active'        => 'boolean',
    ];

    /** Get the currently active project */
    public static function active(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /** Switch active project */
    public static function switchTo(int $id): void
    {
        static::query()->update(['is_active' => false]);
        static::where('id', $id)->update(['is_active' => true]);
    }
}
