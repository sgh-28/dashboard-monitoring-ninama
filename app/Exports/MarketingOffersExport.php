<?php

namespace App\Exports;

use App\Models\MarketingOffer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Carbon\Carbon;

class MarketingOffersExport implements FromCollection, WithHeadings, WithMapping, WithTitle
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
        $query = MarketingOffer::with(['employee']);

        if ($this->category) {
            $query->where('category', $this->category);
        }

        if ($this->status && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->orderByDesc('offer_date')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Perusahaan',
            'Alamat',
            'Contact Person',
            'Jabatan Contact',
            'Bidang',
            'Status',
            'Estimasi Nilai (Rp)',
            'Tanggal Penawaran',
            'Follow Up',
            'Alasan',
            'Marketing',
            'Dibuat Pada',
        ];
    }

    public function map($offer): array
    {
        return [
            $offer->id,
            $offer->company_name,
            $offer->company_address,
            $offer->contact_person ?? '-',
            $offer->contact_position ?? '-',
            ucfirst($offer->category),
            $offer->status_label,
            $offer->estimated_value ? 'Rp ' . number_format($offer->estimated_value, 0, ',', '.') : '-',
            Carbon::parse($offer->offer_date)->format('d/m/Y'),
            $offer->follow_up_date ? Carbon::parse($offer->follow_up_date)->format('d/m/Y') : '-',
            $offer->reason ?? '-',
            $offer->employee?->name ?? '-',
            Carbon::parse($offer->created_at)->format('d/m/Y H:i'),
        ];
    }

    public function title(): string
    {
        $cat = $this->category ? ucfirst($this->category) : 'Semua';
        return "Laporan Marketing - {$cat}";
    }
}
