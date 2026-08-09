<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDivision extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'progress',
    ];

    protected $casts = [
        'progress' => 'float',
    ];

    /**
     * Relasi ke Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relasi ke Tasks (WAJIB specify foreign key: division_id)
     */
    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'division_id'); // ✅ Explicit foreign key
    }

    /**
     * Get completed tasks count
     */
    public function getCompletedTasksCountAttribute()
    {
        return $this->tasks()->where('status', 'done')->count();
    }

    /**
     * Get total tasks count
     */
    public function getTotalTasksCountAttribute()
    {
        return $this->tasks()->count();
    }

    public function getSlaSummaryAttribute(): array
    {
        $tasks = $this->relationLoaded('tasks')
            ? $this->tasks
            : $this->tasks()->get();

        $evaluatedTasks = $tasks->filter(fn(ProjectTask $task) => $task->task_sla_percentage !== null);
        $totalSlaPoints = round($evaluatedTasks->sum(fn(ProjectTask $task) => $task->task_sla_percentage), 2);
        $evaluatedCount = $evaluatedTasks->count();

        return [
            'division_name' => $this->name,
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
        ];
    }

    public function getDivisionSlaPercentageAttribute(): ?float
    {
        return $this->sla_summary['sla_percentage'];
    }

    public function getDivisionSlaPercentageFormattedAttribute(): string
    {
        $percentage = $this->division_sla_percentage;

        return $percentage === null
            ? 'Belum tersedia'
            : ProjectTask::formatSlaPercentage($percentage);
    }
}
