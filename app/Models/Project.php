<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\Cache;

class Project extends Model
{
    protected static $activeProject = null;
    protected static $enabledProjects = null;

    protected static function booted()
    {
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    public static function clearCache(): void
    {
        static::$activeProject = null;
        static::$enabledProjects = null;
        Cache::forget('active_project');
        Cache::forget('enabled_projects');
    }

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
        if (static::$activeProject !== null) {
            return static::$activeProject;
        }

        static::$activeProject = Cache::remember('active_project', 3600, function () {
            return static::where('is_active', true)->first();
        });

        return static::$activeProject;
    }

    /** Get enabled projects */
    public static function enabled(): \Illuminate\Database\Eloquent\Collection
    {
        if (static::$enabledProjects !== null) {
            return static::$enabledProjects;
        }

        static::$enabledProjects = Cache::remember('enabled_projects', 3600, function () {
            return static::where('is_enabled', true)->get();
        });

        return static::$enabledProjects;
    }

    /** Switch active project */
    public static function switchTo(int $id): void
    {
        static::query()->update(['is_active' => false]);
        static::where('id', $id)->update(['is_active' => true]);
        static::clearCache();
    }
}
