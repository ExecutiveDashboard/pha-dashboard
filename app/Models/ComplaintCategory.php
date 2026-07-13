<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplaintCategory extends Model
{
    protected static function booted()
    {
        static::addGlobalScope('project', function ($builder) {
            $activeProject = \App\Models\Project::active();
            if ($activeProject) {
                $builder->where('complaint_categories.project_id', $activeProject->id);
            }
        });
    }

    protected $fillable = ['project_id', 'name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
