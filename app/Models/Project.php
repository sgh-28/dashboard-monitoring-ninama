<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Services\ProjectProgressService;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'status',
        'client_name',
        'customer_id',
        'address',
        'description',
        'progress',
        'start_date',
        'end_date',
        'deadline',
        'sla',
        'rejection_reason',
        'status_text',
        'completed_by',
        'completed_at',
        'customer_feedback',
        'customer_satisfaction_rating',
        'customer_feedback_submitted_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deadline' => 'date',
        'completed_at' => 'datetime',
        'customer_feedback_submitted_at' => 'datetime',
        'progress' => 'float',
        'sla' => 'integer',
    ];

    // ==========================================
    // RELASI
    // ==========================================
    
    /**
     * Relasi ke Project Phases (Timeline Proyek)
     */
    public function phases()
    {
        return $this->hasMany(ProjectPhase::class)->orderBy('phase_order');
    }

    /**
     * Relasi ke Project Divisions
     */
    public function divisions()
    {
        return $this->hasMany(ProjectDivision::class);
    }

    /**
     * ✅ DIPERBAIKI: Relasi ke Project Tasks (langsung, tanpa hasManyThrough)
     */
    public function tasks()
    {
        return $this->hasMany(ProjectTask::class);
    }

    /**
     * Relasi ke Customer (User)
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * ✅ BARU: Relasi ke Project Milestones
     */
    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    /**
     * ✅ BARU: Relasi ke Notifications (melalui tasks)
     */
    public function notifications()
    {
        return $this->hasManyThrough(
            Notification::class,
            ProjectTask::class,
            'project_id', // Foreign key di project_tasks
            'project_task_id', // Foreign key di notifications
            'id', // Local key di projects
            'id' // Local key di project_tasks
        );
    }

    // ==========================================
    // LOGIC: Overall Progress & SLA
    // ==========================================

    /**
     * Hitung Overall Progress dari rata-rata progress divisi.
     */
    public function getOverallProgressAttribute()
    {
        return ProjectProgressService::projectProgress($this);
    }

    /**
     * SLA Overall Project (Berdasarkan status SLA phases-nya)
     */
    public function getProjectSlaStatusAttribute()
    {
        if ($this->phases->count() === 0) return 'on_track';

        $breached = $this->phases->where('sla_status', 'breached')->count();
        $warning = $this->phases->where('sla_status', 'warning')->count();
        
        if ($breached > 0) return 'breached';
        if ($warning > 0) return 'warning';
        return 'on_track';
    }

    public function getSlaSummaryAttribute(): array
    {
        $tasks = $this->slaTasks();
        $evaluatedTasks = $tasks->filter(fn(ProjectTask $task) => $task->task_sla_percentage !== null);
        $totalSlaPoints = round($evaluatedTasks->sum(fn(ProjectTask $task) => $task->task_sla_percentage), 2);
        $evaluatedCount = $evaluatedTasks->count();

        return [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'done')->count(),
            'evaluated_tasks' => $evaluatedCount,
            'on_time_tasks' => $tasks->where('sla_evaluation_status', 'completed_on_time')->count(),
            'late_tasks' => $tasks->where('sla_evaluation_status', 'completed_late')->count(),
            'on_track_tasks' => $tasks->where('sla_evaluation_status', 'on_track')->count(),
            'warning_tasks' => $tasks->where('sla_evaluation_status', 'warning')->count(),
            'breached_tasks' => $tasks->where('sla_evaluation_status', 'breached')->count(),
            'sla_points' => $totalSlaPoints,
            'sla_percentage' => $evaluatedCount > 0 ? round($totalSlaPoints / $evaluatedCount, 2) : null,
            'is_final' => $tasks->count() > 0
                && $this->status === 'done'
                && filled($this->completed_by)
                && filled($this->completed_at)
                && $this->completedByIsAuthorizedProjectManagement()
                && $tasks->every(fn(ProjectTask $task) => $task->status === 'done')
                && $tasks->every(fn(ProjectTask $task) => $task->verification_status === 'approved'),
        ];
    }

    public function getDivisionSlaSummariesAttribute(): Collection
    {
        if ($this->relationLoaded('divisions')) {
            $divisions = $this->divisions;
        } elseif (!$this->exists) {
            $divisions = collect();
        } else {
            $divisions = $this->divisions()->with('tasks')->get();
        }

        return $divisions
            ->filter(fn($division) => $division instanceof ProjectDivision)
            ->map(fn(ProjectDivision $division) => $division->sla_summary)
            ->filter(fn($summary) => is_array($summary))
            ->values();
    }

    public function getTotalTasksCountAttribute(): int
    {
        return $this->sla_summary['total_tasks'];
    }

    public function getEvaluatedTasksCountAttribute(): int
    {
        return $this->sla_summary['evaluated_tasks'];
    }

    public function getOnTimeTasksCountAttribute(): int
    {
        return $this->sla_summary['on_time_tasks'];
    }

    public function getLateTasksCountAttribute(): int
    {
        return $this->sla_summary['late_tasks'];
    }

    public function getBreachedTasksCountAttribute(): int
    {
        return $this->sla_summary['breached_tasks'];
    }

    public function getProjectSlaPercentageAttribute(): ?float
    {
        return $this->sla_summary['sla_percentage'];
    }

    public function getSlaPercentageAttribute(): ?float
    {
        return $this->project_sla_percentage;
    }

    public function getSlaIsFinalAttribute(): bool
    {
        return $this->sla_summary['is_final'];
    }

    public function getSlaStatusTextAttribute(): string
    {
        return $this->sla_is_final ? 'SLA Final' : 'SLA Sementara';
    }

    public function getSlaPercentageFormattedAttribute(): string
    {
        return $this->project_sla_percentage === null
            ? 'Belum tersedia'
            : ProjectTask::formatSlaPercentage($this->project_sla_percentage);
    }

    private function slaTasks(): Collection
    {
        if ($this->relationLoaded('tasks')) {
            return $this->tasks;
        }

        if (!$this->exists) {
            return collect();
        }

        return $this->tasks()->get();
    }

    private function completedByIsAuthorizedProjectManagement(): bool
    {
        if (!$this->completed_by) {
            return false;
        }

        $user = $this->relationLoaded('completedBy')
            ? $this->completedBy
            : $this->completedBy()->first();

        if (!$user) {
            return false;
        }

        return $user->hasRole('pegawai')
            && strcasecmp(trim((string) $user->jabatan), 'Project Management') === 0
            && $user->bidang === $this->category;
    }

    // ==========================================
    // LOGIC LAMA (TETAP DIPERTAHANKAN)
    // ==========================================

    public function isOngoing()
    {
        return $this->status === 'ongoing';
    }

    public function isCompleted()
    {
        return $this->status === 'done';
    }

    public function updateProjectProgress()
    {
        ProjectProgressService::syncProject($this);
    }

    public function getDaysUntilDeadlineAttribute()
    {
        if (!$this->deadline) return null;
        
        return now()->diffInDays($this->deadline, false);
    }

    public function isDeadlineApproaching()
    {
        return $this->deadline && 
               $this->days_until_deadline !== null && 
               $this->days_until_deadline <= 3 && 
               $this->days_until_deadline >= 0;
    }

    public function isOverdue()
    {
        return $this->deadline && 
               $this->deadline->isPast() && 
               !$this->isCompleted();
    }
}
