<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\ProjectTask;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class ProjectsExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $category;
    protected $status;

    public function __construct($category = null, $status = null)
    {
        $this->category = $category;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Project::with(['customer', 'tasks', 'divisions.tasks']);

        if ($this->category) {
            $query->where('category', $this->category);
        }

        if ($this->status && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Proyek',
            'Kategori',
            'Customer',
            'Status',
            'Progress (%)',
            'Tanggal Mulai',
            'Deadline',
            'SLA Proyek',
            'Status SLA',
            'Task Tepat Waktu',
            'Total Task',
            'Task Dinilai',
            'Task Terlambat',
            'Task Breached',
            'SLA per Divisi',
            'Sisa Hari',
            'Dibuat Pada',
        ];
    }

    public function map($project): array
    {
        $daysLeft = $project->deadline ? now()->diffInDays($project->deadline, false) : null;

        return [
            $project->id,
            $project->name,
            ucfirst($project->category),
            $project->customer?->company ?? '-',
            ucfirst($project->status),
            $project->progress,
            $project->start_date ? Carbon::parse($project->start_date)->format('d/m/Y') : '-',
            $project->deadline ? Carbon::parse($project->deadline)->format('d/m/Y') : '-',
            $project->sla_percentage_formatted,
            $project->sla_status_text,
            $project->on_time_tasks_count,
            $project->total_tasks_count,
            $project->evaluated_tasks_count,
            $project->late_tasks_count,
            $project->breached_tasks_count,
            $project->division_sla_summaries
                ->filter(fn($division) => is_array($division))
                ->map(fn($division) => ($division['division_name'] ?? '-') . ': ' . (($division['sla_percentage'] ?? null) === null ? 'Belum tersedia' : ProjectTask::formatSlaPercentage($division['sla_percentage'])))
                ->implode('; '),
            $daysLeft !== null ? $daysLeft . ' hari' : '-',
            Carbon::parse($project->created_at)->format('d/m/Y H:i'),
        ];
    }

    public function title(): string
    {
        $cat = $this->category ? ucfirst($this->category) : 'Semua';
        return "Laporan Proyek - {$cat}";
    }
}
