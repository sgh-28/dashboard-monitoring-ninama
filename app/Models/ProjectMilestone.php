<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProjectMilestone extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'planned_date',
        'actual_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'planned_date' => 'date',
        'actual_date' => 'date',
    ];

    /**
     * Get the project that owns the milestone.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Check if milestone is delayed
     */
    public function isDelayed(): bool
    {
        if (!$this->planned_date) return false;
        
        // Jika belum selesai dan sudah lewat tanggal rencana
        if ($this->status !== 'completed' && $this->planned_date->isPast()) {
            return true;
        }
        
        // Jika sudah selesai tapi actual_date > planned_date
        if ($this->actual_date && $this->planned_date && $this->actual_date->gt($this->planned_date)) {
            return true;
        }
        
        return false;
    }

    /**
     * Get days until planned date (negative if overdue)
     */
    public function getDaysUntilPlannedAttribute(): ?int
    {
        if (!$this->planned_date) return null;
        return now()->diffInDays($this->planned_date, false);
    }

    /**
     * Get delay duration in days
     */
    public function getDelayDaysAttribute(): ?int
    {
        if (!$this->isDelayed()) return 0;
        
        if ($this->actual_date) {
            return $this->planned_date->diffInDays($this->actual_date);
        }
        
        return now()->diffInDays($this->planned_date);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'ongoing' => 'Sedang Berjalan',
            'completed' => 'Selesai',
            'delayed' => 'Terlambat',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'completed' => 'bg-green-500/20 text-green-400',
            'ongoing' => 'bg-blue-500/20 text-blue-400',
            'delayed' => 'bg-red-500/20 text-red-400',
            'pending' => 'bg-gray-500/20 text-gray-400',
            default => 'bg-gray-500/20 text-gray-400',
        };
    }

    /**
     * Scope: Get milestones by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Get delayed milestones
     */
    public function scopeDelayed($query)
    {
        return $query->where('status', 'delayed')
                    ->orWhere(function($q) {
                        $q->where('status', '!=', 'completed')
                          ->where('planned_date', '<', now());
                    });
    }
}