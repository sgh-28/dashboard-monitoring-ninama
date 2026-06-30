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

    public function getDisplayNameAttribute(): string
    {
        $category = $this->project?->category;
        $templates = self::phaseTemplates();

        return $templates[$category][$this->phase_order - 1]['name']
            ?? $this->phase_name;
    }

    public static function phaseTemplates(): array
    {
        return [
            'web' => [
                ['name' => 'Analisis Kebutuhan', 'division' => 'Project Management', 'days' => 6],
                ['name' => 'Desain UI/UX', 'division' => 'UI/UX', 'days' => 8],
                ['name' => 'Pengembangan Aplikasi', 'division' => 'Frontend', 'days' => 14],
                ['name' => 'Testing Aplikasi', 'division' => 'Testing', 'days' => 7],
                ['name' => 'Deployment Aplikasi', 'division' => 'DevOps', 'days' => 4],
            ],
            'internet' => [
                ['name' => 'Survey Kebutuhan Jaringan', 'division' => 'Project Management', 'days' => 5],
                ['name' => 'Perancangan Topologi Jaringan', 'division' => 'Network Engineer', 'days' => 7],
                ['name' => 'Instalasi Infrastruktur Jaringan', 'division' => 'Fiber Optic Technician', 'days' => 14],
                ['name' => 'Konfigurasi & Monitoring NOC', 'division' => 'NOC', 'days' => 7],
                ['name' => 'Serah Terima & Technical Support', 'division' => 'Technical Support', 'days' => 4],
            ],
            'cctv' => [
                ['name' => 'Survey Titik Kamera', 'division' => 'Project Management', 'days' => 5],
                ['name' => 'Perencanaan Jalur & Perangkat CCTV', 'division' => 'CCTV Installer', 'days' => 6],
                ['name' => 'Pemasangan Kamera CCTV', 'division' => 'CCTV Installer', 'days' => 12],
                ['name' => 'Konfigurasi NVR & Monitoring', 'division' => 'Configuration', 'days' => 7],
                ['name' => 'Testing & Maintenance CCTV', 'division' => 'Maintenance', 'days' => 4],
            ],
        ];
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
