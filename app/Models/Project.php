<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'deadline' => 'date',
        'progress' => 'integer',
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
     * Hitung Overall Progress dari rata-rata phases
     */
    public function getOverallProgressAttribute()
    {
        if ($this->phases->count() === 0) return $this->progress; // Fallback ke kolom progress jika belum ada phases
        
        // Jika ada phases, hitung rata-rata dari phases
        return round($this->phases->avg('progress'));
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
        $divisions = $this->divisions;
        
        if ($divisions->count() > 0) {
            $totalProgress = $divisions->sum('progress');
            $averageProgress = round($totalProgress / $divisions->count());
            
            $this->update(['progress' => $averageProgress]);
        }
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