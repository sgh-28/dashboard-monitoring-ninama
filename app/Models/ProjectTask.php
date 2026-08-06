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

    public function isCompletedOnTime(): bool
    {
        $targetDate = $this->slaTargetDate();
        $completionDate = $this->slaCompletionDate();

        if ($this->status !== 'done' || !$completionDate || !$targetDate) {
            return false;
        }

        return $completionDate->lte($targetDate);
    }

    public function slaPlannedStartDate(): ?Carbon
    {
        return $this->planned_start_date
            ? Carbon::parse($this->planned_start_date)->startOfDay()
            : null;
    }

    public function slaTargetDate(): ?Carbon
    {
        $targetDate = $this->planned_end_date ?: $this->deadline;

        return $targetDate ? Carbon::parse($targetDate)->startOfDay() : null;
    }

    public function slaCompletionDate(): ?Carbon
    {
        $completionDate = $this->actual_end_date ?: $this->completed_at;

        return $completionDate ? Carbon::parse($completionDate)->startOfDay() : null;
    }

    public function getSlaTargetDaysAttribute(): ?int
    {
        $startDate = $this->slaPlannedStartDate();
        $targetDate = $this->slaTargetDate();

        if (!$startDate || !$targetDate || $targetDate->lt($startDate)) {
            return null;
        }

        return max(1, (int) $startDate->diffInDays($targetDate) + 1);
    }

    public function getSlaEvaluationStatusAttribute(): string
    {
        $startDate = $this->slaPlannedStartDate();
        $targetDate = $this->slaTargetDate();

        if (!$startDate || !$targetDate || $targetDate->lt($startDate)) {
            return 'not_available';
        }

        if ($this->status === 'done') {
            return $this->isCompletedOnTime() ? 'completed_on_time' : 'completed_late';
        }

        $today = Carbon::now()->startOfDay();
        if ($today->gt($targetDate)) {
            return 'breached';
        }

        if ($this->status === 'pending') {
            return 'pending';
        }

        $targetDays = $this->sla_target_days;
        if ($targetDays) {
            $elapsedDays = $today->lt($startDate)
                ? 0
                : min($targetDays, (int) $startDate->diffInDays($today) + 1);

            if (($elapsedDays / $targetDays) >= 0.8) {
                return 'warning';
            }
        }

        return 'on_track';
    }

    public function getSlaEvaluationReasonAttribute(): ?string
    {
        if (!$this->slaPlannedStartDate()) {
            return 'Tanggal mulai rencana belum diisi.';
        }

        if (!$this->slaTargetDate()) {
            return 'Tanggal target/deadline belum diisi.';
        }

        if ($this->slaTargetDate()->lt($this->slaPlannedStartDate())) {
            return 'Tanggal target lebih awal dari tanggal mulai rencana.';
        }

        if ($this->status === 'pending') {
            return 'Task belum dimulai.';
        }

        if (!in_array($this->sla_evaluation_status, ['completed_on_time', 'completed_late', 'breached'], true)) {
            return 'Task belum selesai dan deadline belum terlewati.';
        }

        return null;
    }

    public function getLateDaysAttribute(): ?int
    {
        $targetDate = $this->slaTargetDate();
        $evaluationDate = $this->slaEvaluationDate();

        if (!$targetDate || !$evaluationDate) {
            return null;
        }

        return max(0, (int) $targetDate->diffInDays($evaluationDate, false));
    }

    public function slaEvaluationDate(): ?Carbon
    {
        $targetDate = $this->slaTargetDate();

        if (!$targetDate) {
            return null;
        }

        if ($this->status === 'done') {
            return $this->slaCompletionDate();
        }

        if (Carbon::now()->startOfDay()->gt($targetDate)) {
            return Carbon::now()->startOfDay();
        }

        return null;
    }

    public function getTaskSlaPercentageAttribute(): ?float
    {
        $targetDays = $this->sla_target_days;
        $lateDays = $this->late_days;

        if ($targetDays === null || $lateDays === null) {
            return null;
        }

        if ($lateDays === 0) {
            return 100.0;
        }

        $percentage = round(($targetDays / ($targetDays + $lateDays)) * 100, 2);

        return min(100.0, max(0.0, $percentage));
    }

    public function getTaskSlaPercentageFormattedAttribute(): string
    {
        return $this->task_sla_percentage === null
            ? 'Belum tersedia'
            : self::formatSlaPercentage($this->task_sla_percentage);
    }

    public static function formatSlaPercentage(float $value): string
    {
        $formatted = number_format($value, 2, ',', '.');
        $formatted = rtrim(rtrim($formatted, '0'), ',');

        return $formatted . '%';
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
