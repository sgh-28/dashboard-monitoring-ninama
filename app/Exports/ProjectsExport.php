<?php

namespace App\Exports;

use App\Models\Project;
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
        $query = Project::with(['customer', 'phases']);

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
            'Target SLA (%)',
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
            $project->sla ?? '-',
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