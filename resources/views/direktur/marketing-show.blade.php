@extends('layouts.app')

@section('title', 'Detail Laporan Marketing')

@section('content')
<div class="p-6 max-w-6xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('direktur.dashboard') }}" class="text-blue-400 hover:underline text-sm">Kembali ke Dashboard Direktur</a>
        <h1 class="text-2xl font-bold text-white mt-2">Detail Laporan Marketing</h1>
        <p class="text-gray-400 text-sm">{{ $offer->company_name }}</p>
    </div>

    @php
        $statusClasses = [
            'penawaran' => 'bg-blue-900/50 text-blue-300',
            'follow_up' => 'bg-yellow-900/50 text-yellow-300',
            'meeting' => 'bg-purple-900/50 text-purple-300',
            'menunggu_keputusan' => 'bg-gray-700 text-gray-300',
            'negosiasi' => 'bg-orange-900/50 text-orange-300',
            'deal' => 'bg-green-900/50 text-green-300',
            'pending' => 'bg-yellow-900/50 text-yellow-300',
            'rejected' => 'bg-red-900/50 text-red-300',
            'no_response' => 'bg-red-900/50 text-red-300',
        ];
        $histories = $offer->histories ?? collect();
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-white">{{ $offer->company_name }}</h2>
                        <p class="text-sm text-gray-400">{{ $offer->company_address ?: '-' }}</p>
                    </div>
                    <span class="w-fit px-3 py-1 text-xs rounded-full {{ $statusClasses[$offer->status] ?? 'bg-gray-700 text-gray-300' }}">
                        {{ $offer->status_label }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Nama PIC</p>
                        <p class="text-white font-medium">{{ $offer->contact_person ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Jabatan PIC</p>
                        <p class="text-white font-medium">{{ $offer->contact_position ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Nomor Kontak</p>
                        <p class="text-white font-medium">{{ $offer->contact_phone ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Email</p>
                        <p class="text-white font-medium">{{ $offer->contact_email ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Bidang</p>
                        <p class="text-white font-medium">{{ ucfirst($offer->category) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Marketing</p>
                        <p class="text-white font-medium">{{ $offer->employee?->name ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Tanggal Penawaran</p>
                        <p class="text-white font-medium">{{ $offer->offer_date?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Tanggal Follow Up</p>
                        <p class="text-white font-medium">{{ $offer->follow_up_date?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Jadwal Meeting</p>
                        <p class="text-white font-medium">{{ $offer->meeting_date?->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Estimasi Nilai</p>
                        <p class="text-white font-medium">
                            {{ $offer->estimated_value ? 'Rp ' . number_format((float) $offer->estimated_value, 0, ',', '.') : '-' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Detail Penawaran</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Daftar / Detail Penawaran</p>
                        <p class="text-sm text-gray-300 whitespace-pre-line">{{ $offer->offer_description ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Alasan / Kendala</p>
                        <p class="text-sm text-gray-300 whitespace-pre-line">{{ $offer->reason ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Catatan Terbaru</p>
                        <p class="text-sm text-gray-300 whitespace-pre-line">{{ $offer->notes ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-gray-800 rounded-lg border border-gray-700 p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Riwayat Update</h2>

                @if($histories->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($histories->sortByDesc('created_at') as $history)
                            <div class="rounded-lg border border-gray-700 bg-gray-900/40 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-white">{{ $history->status_label }}</p>
                                        <p class="text-xs text-gray-400">Follow up: {{ $history->follow_up_date?->format('d/m/Y') ?? '-' }}</p>
                                    </div>
                                    <p class="text-xs text-gray-500">Diupdate: {{ $history->created_at?->format('d/m/Y H:i') }}</p>
                                </div>
                                <p class="mt-3 text-xs text-gray-500">Keterangan:</p>
                                <p class="text-sm text-gray-300 whitespace-pre-line">{{ $history->notes ?: '-' }}</p>
                                <p class="mt-2 text-xs text-gray-500">Diubah oleh: {{ $history->changedBy?->name ?? 'Marketing' }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">Belum ada riwayat update.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
