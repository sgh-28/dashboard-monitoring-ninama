<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProjectTask extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'project_id',
        'division_id',
        'title',
        'description',
        'assigned_to',
        'deadline',
        'status',
        'verification_status',
        'progress',
        'proof_image',
        'completion_notes',
        'verification_notes',
        'verified_by',
        'verified_at',
        'completed_at',
        // ✅ NEW FIELDS FOR TIMELINE & SLA
        'sla_target',
        'planned_start_date',
        'planned_end_date',
        'actual_start_date',
        'actual_end_date',
        'delay_reason',
        'is_notified',
        'google_event_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'deadline' => 'date',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'progress' => 'integer',
        // ✅ NEW CASTS
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'sla_target' => 'integer',
        'is_notified' => 'boolean',
    ];

    /**
     * Get the project that owns the task.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the division that owns the task.
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(ProjectDivision::class);
    }

    /**
     * Get the user assigned to this task (Pegawai or Marketing).
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get milestones for this task's project.
     */
    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class, 'project_id', 'project_id');
    }

    /**
     * Scope a query to only include tasks assigned to a specific user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to only include tasks for a specific project.
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope a query to only include tasks with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to order by deadline (urgent first).
     */
    public function scopeOrderByDeadline($query, $direction = 'asc')
    {
        return $query->orderBy('deadline', $direction);
    }

    /**
     * Check if the task is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->deadline 
            && $this->deadline->isPast() 
            && !in_array($this->status, ['done', 'completed']);
    }

    /**
     * Check if the task is due soon (within 3 days).
     */
    public function isDueSoon(): bool
    {
        return $this->deadline 
            && $this->deadline->diffInDays(now(), false) <= 3 
            && $this->deadline->isFuture()
            && !in_array($this->status, ['done', 'completed']);
    }

    /**
     * Check if task is delayed (actual > planned).
     */
    public function isDelayed(): bool
    {
        return $this->actual_end_date 
            && $this->planned_end_date 
            && $this->actual_end_date->gt($this->planned_end_date);
    }

    /**
     * Get the number of days until deadline (negative if overdue).
     */
    public function getDaysUntilDeadlineAttribute(): ?int
    {
        if (!$this->deadline) return null;
        return now()->diffInDays($this->deadline, false);
    }

    /**
     * Get the status badge color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'done', 'completed' => 'bg-green-500/20 text-green-400',
            'ongoing', 'in_progress' => 'bg-blue-500/20 text-blue-400',
            'pending' => 'bg-gray-500/20 text-gray-400',
            default => 'bg-gray-500/20 text-gray-400',
        };
    }

    /**
     * Get the status label for display.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'done', 'completed' => 'Selesai',
            'ongoing', 'in_progress' => 'Sedang Dikerjakan',
            'pending' => 'Menunggu',
            default => ucfirst($this->status),
        };
    }

    public function getVerificationStatusLabelAttribute(): string
    {
        return match($this->verification_status) {
            'pending_review' => 'Menunggu Verifikasi PM',
            'approved' => 'Disetujui PM',
            default => 'Belum Diverifikasi',
        };
    }

    public function getVerificationStatusColorAttribute(): string
    {
        return match($this->verification_status) {
            'pending_review' => 'bg-yellow-500/20 text-yellow-300',
            'approved' => 'bg-green-500/20 text-green-400',
            default => 'bg-gray-500/20 text-gray-400',
        };
    }

    /**
     * Get the proof image URL (if exists).
     */
    public function getProofImageUrlAttribute(): ?string
    {
        return $this->proof_image ? asset('storage/' . $this->proof_image) : null;
    }
}
