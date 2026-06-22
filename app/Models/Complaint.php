<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Complaint extends Model
{
    protected $fillable = [
        'complaint_number',
        'project_id',
        'allottee_id',
        'category_id',
        'subject',
        'description',
        'priority',
        'status',
        'assigned_staff_id',
        'satisfaction_confirmed',
        'feedback_remarks',
        'resolved_at',
        'closed_at'
    ];

    protected $casts = [
        'satisfaction_confirmed' => 'boolean',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope('project', function ($builder) {
            $activeProject = \App\Models\Project::active();
            if ($activeProject) {
                $builder->where('complaints.project_id', $activeProject->id);
            }
        });

        static::creating(function ($complaint) {
            $yearMonth = now()->format('Ym');
            // count complaints in current month
            $count = static::withoutGlobalScopes()
                ->where('complaint_number', 'like', "CMS-{$yearMonth}-%")
                ->count();
            
            $nextNumber = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
            $complaint->complaint_number = "CMS-{$yearMonth}-{$nextNumber}";
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function allottee(): BelongsTo
    {
        return $this->belongsTo(Allottee::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'category_id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(MaintenanceStaff::class, 'assigned_staff_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(ComplaintAttachment::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ComplaintLog::class)->orderBy('created_at', 'asc');
    }

    /** Helper to check current status color class */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'new' => 'bg-primary',
            'under_review' => 'bg-info text-dark',
            'assigned' => 'bg-warning text-dark',
            'in_progress' => 'bg-secondary',
            'waiting_for_material' => 'bg-warning text-dark border border-warning',
            'pending_external_vendor' => 'bg-dark',
            'resolved' => 'bg-success',
            'closed' => 'bg-light text-dark border',
            'rejected' => 'bg-danger',
            'reopened' => 'bg-warning text-dark fw-bold border border-danger',
            default => 'bg-secondary'
        };
    }

    /** Helper to format priority badge */
    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'bg-success bg-opacity-10 text-success border border-success',
            'medium' => 'bg-info bg-opacity-10 text-info border border-info',
            'high' => 'bg-warning bg-opacity-10 text-warning border border-warning',
            'emergency' => 'bg-danger bg-opacity-10 text-danger border border-danger animate-pulse',
            default => 'bg-secondary bg-opacity-10 text-dark border'
        };
    }
}
