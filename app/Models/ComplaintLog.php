<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintLog extends Model
{
    protected $fillable = [
        'complaint_id',
        'user_id',
        'allottee_id',
        'action',
        'status_from',
        'status_to',
        'remarks'
    ];

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function allottee(): BelongsTo
    {
        return $this->belongsTo(Allottee::class);
    }

    /** Display name of who performed the action */
    public function getActorNameAttribute(): string
    {
        if ($this->user_id) {
            return $this->user->name . ' (Staff/Admin)';
        }
        if ($this->allottee_id) {
            return $this->allottee->name . ' (Allottee)';
        }
        return 'System';
    }

    /** Icon for the activity timeline */
    public function getIconAttribute(): string
    {
        return match ($this->action) {
            'created' => 'bi-plus-circle-fill text-primary',
            'assigned' => 'bi-person-plus-fill text-warning',
            'status_changed' => 'bi-arrow-right-circle-fill text-secondary',
            'remarked' => 'bi-chat-left-text-fill text-info',
            'resolved' => 'bi-check-circle-fill text-success',
            'closed' => 'bi-lock-fill text-dark',
            'reopened' => 'bi-arrow-counterclockwise text-danger',
            'feedback_submitted' => 'bi-star-fill text-warning',
            default => 'bi-info-circle-fill'
        };
    }
}
