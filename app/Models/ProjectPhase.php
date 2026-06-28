<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'phase_name', 'phase_order', 'status', 'progress',
        'start_date', 'target_date', 'completed_date',
        'sla_days', 'actual_days', 'sla_status', 'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'target_date' => 'date',
        'completed_date' => 'date',
        'progress' => 'integer',
        'phase_order' => 'integer',
    ];

    /**
     * Relasi ke Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope: Urutkan berdasarkan phase_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('phase_order');
    }

    /**
     * Hitung SLA otomatis
     */
    public function calculateSla()
    {
        if ($this->start_date && $this->target_date) {
            $this->sla_days = $this->start_date->diffInDays($this->target_date);
        }

        if ($this->start_date && $this->completed_date) {
            $this->actual_days = $this->start_date->diffInDays($this->completed_date);
        }

        // Update SLA Status
        if ($this->status === 'completed') {
            $this->sla_status = 'completed';
        } elseif ($this->actual_days && $this->sla_days) {
            if ($this->actual_days > $this->sla_days) {
                $this->sla_status = 'breached';
            } elseif ($this->actual_days > ($this->sla_days * 0.8)) {
                $this->sla_status = 'warning';
            } else {
                $this->sla_status = 'on_track';
            }
        }

        $this->save();
    }

    /**
     * Label Status SLA
     */
    public function getSlaStatusLabelAttribute()
    {
        $labels = [
            'on_track' => 'On Track',
            'warning' => 'Warning',
            'breached' => 'Breached',
            'completed' => 'Completed',
        ];
        return $labels[$this->sla_status] ?? $this->sla_status;
    }

    /**
     * Warna Status SLA
     */
    public function getSlaStatusColorAttribute()
    {
        $colors = [
            'on_track' => 'text-green-400',
            'warning' => 'text-yellow-400',
            'breached' => 'text-red-400',
            'completed' => 'text-blue-400',
        ];
        return $colors[$this->sla_status] ?? 'text-gray-400';
    }
}