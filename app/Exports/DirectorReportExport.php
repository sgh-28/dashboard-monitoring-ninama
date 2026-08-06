<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\MarketingOffer;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DirectorReportExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            0 => new ProjectsSheet(),
            1 => new MarketingSheet(),
        ];
    }
}

class ProjectsSheet implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle
{
    public function headings(): array
    {
        return [
            'Nama Proyek',
            'Kategori',
            'Status',
            'Client',
            'Deadline',
            'Progress (%)',
            'SLA Proyek',
            'Status SLA',
            'Task Tepat Waktu',
            'Task Terlambat',
            'Task Breached',
            'Task Dinilai',
            'Total Task',
            'SLA per Divisi',
            'Tanggal Dibuat'
        ];
    }

    public function array(): array
    {
        return Project::with(['tasks', 'divisions.tasks'])
            ->select('id', 'name', 'category', 'status', 'client_name', 'deadline', 'progress', 'created_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'category' => ucfirst($item->category),
                    'status' => ucfirst(str_replace('_', ' ', $item->status)),
                    'client_name' => $item->client_name,
                    'deadline' => $item->deadline ? \Carbon\Carbon::parse($item->deadline)->format('d/m/Y') : '-',
                    'progress' => $item->progress,
                    'sla_actual' => $item->sla_percentage_formatted,
                    'sla_status' => $item->sla_status_text,
                    'on_time_tasks' => $item->on_time_tasks_count,
                    'late_tasks' => $item->late_tasks_count,
                    'breached_tasks' => $item->breached_tasks_count,
                    'evaluated_tasks' => $item->evaluated_tasks_count,
                    'total_tasks' => $item->total_tasks_count,
                    'division_sla' => $item->division_sla_summaries
                        ->map(fn($division) => $division['division_name'] . ': ' . ($division['sla_percentage'] === null ? 'Belum tersedia' : ProjectTask::formatSlaPercentage($division['sla_percentage'])))
                        ->implode('; '),
                    'created_at' => \Carbon\Carbon::parse($item->created_at)->format('d/m/Y')
                ];
            })
            ->toArray();
    }

    public function title(): string
    {
        return 'Data Proyek';
    }
}

class MarketingSheet implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle
{
    public function headings(): array
    {
        return [
            'Nama Perusahaan',
            'Bidang',
            'Status Penawaran',
            'Nilai Estimasi (Rp)',
            'Tanggal Penawaran',
            'Kontak PIC'
        ];
    }

    public function array(): array
    {
        return MarketingOffer::select('company_name', 'category', 'status', 'estimated_value', 'offer_date', 'contact_person')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'company_name' => $item->company_name,
                    'category' => ucfirst($item->category),
                    'status' => ucfirst(str_replace('_', ' ', $item->status)),
                    'estimated_value' => $item->estimated_value ? 'Rp ' . number_format($item->estimated_value, 0, ',', '.') : '-',
                    'offer_date' => $item->offer_date ? \Carbon\Carbon::parse($item->offer_date)->format('d/m/Y') : '-',
                    'contact_person' => $item->contact_person ?? '-'
                ];
            })
            ->toArray();
    }

    public function title(): string
    {
        return 'Marketing';
    }
}
