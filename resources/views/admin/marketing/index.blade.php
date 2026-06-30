@extends('layouts.app')

@section('title', 'Laporan Marketing - Admin')

@section('content')
<div class="p-6">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Laporan Penawaran Marketing</h1>
            <p class="text-gray-400 text-sm">Monitor laporan marketing, status penawaran, dan tindak lanjut pembuatan project.</p>
        </div>
        <a href="{{ route('admin.marketing.export') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition text-sm font-medium">
            Export Marketing
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
            <p class="text-sm text-gray-400">Total Penawaran</p>
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
            <p class="text-sm text-gray-400">Dalam Proses</p>
            <p class="text-2xl font-bold text-yellow-400">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
            <p class="text-sm text-gray-400">Deal</p>
            <p class="text-2xl font-bold text-green-400">{{ $stats['deal'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
            <p class="text-sm text-gray-400">Perlu Akun</p>
            <p class="text-2xl font-bold text-amber-400">{{ $stats['needs_account'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
            <p class="text-sm text-gray-400">Ditolak / No Response</p>
            <p class="text-2xl font-bold text-red-400">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.marketing.index') }}" class="bg-gray-800 rounded-lg border border-gray-700 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Perusahaan, kontak, alamat..."
                       class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Bidang</label>
                <select name="category" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    <option value="">Semua Bidang</option>
                    <option value="web" {{ request('category') === 'web' ? 'selected' : '' }}>Web & Aplikasi</option>
                    <option value="internet" {{ request('category') === 'internet' ? 'selected' : '' }}>Internet & Jaringan</option>
                    <option value="cctv" {{ request('category') === 'cctv' ? 'selected' : '' }}>CCTV</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Progress</label>
                <select name="status" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white">
                    <option value="">Semua Progress</option>
                    <option value="penawaran" {{ request('status') === 'penawaran' ? 'selected' : '' }}>Penawaran</option>
                    <option value="follow_up" {{ request('status') === 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                    <option value="meeting" {{ request('status') === 'meeting' ? 'selected' : '' }}>Meeting</option>
                    <option value="menunggu_keputusan" {{ request('status') === 'menunggu_keputusan' ? 'selected' : '' }}>Menunggu Keputusan</option>
                    <option value="negosiasi" {{ request('status') === 'negosiasi' ? 'selected' : '' }}>Negosiasi</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="deal" {{ request('status') === 'deal' ? 'selected' : '' }}>Deal</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="no_response" {{ request('status') === 'no_response' ? 'selected' : '' }}>No Response</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">Filter</button>
                <a href="{{ route('admin.marketing.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">Reset</a>
            </div>
        </div>
    </form>

    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-700/60 text-gray-300 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Calon Customer</th>
                        <th class="px-4 py-3 text-left">Bidang</th>
                        <th class="px-4 py-3 text-left">Penawaran</th>
                        <th class="px-4 py-3 text-left">Progress</th>
                        <th class="px-4 py-3 text-left">Jadwal</th>
                        <th class="px-4 py-3 text-left">Project / Task</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($offers as $offer)
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
                            $needsAccount = $offer->needsCustomerAccount();
                            $copyText = "DATA CUSTOMER DARI PENAWARAN MARKETING\n"
                                . "Nama Perusahaan: {$offer->company_name}\n"
                                . "Nama Customer/Kontak: " . ($offer->contact_person ?: '-') . "\n"
                                . "Jabatan: " . ($offer->contact_position ?: '-') . "\n"
                                . "Email: " . ($offer->contact_email ?: '-') . "\n"
                                . "Nomor HP: " . ($offer->contact_phone ?: '-') . "\n"
                                . "Alamat: {$offer->company_address}\n\n"
                                . "DATA PROJECT\n"
                                . "Nama Project: " . ($offer->offer_description ?: $offer->company_name) . "\n"
                                . "Bidang: " . ucfirst($offer->category) . "\n"
                                . "Nilai Penawaran: " . ($offer->estimated_value ? 'Rp ' . number_format((float) $offer->estimated_value, 0, ',', '.') : '-') . "\n"
                                . "Tanggal Deal/Penawaran: " . ($offer->offer_date ? $offer->offer_date->format('d/m/Y') : '-') . "\n"
                                . "Marketing: " . ($offer->employee?->name ?: '-') . "\n"
                                . "Catatan: " . ($offer->notes ?: '-');
                        @endphp
                        <tr class="hover:bg-gray-700/30 transition">
                            <td class="px-4 py-3">
                                <p class="font-medium text-white">{{ $offer->company_name }}</p>
                                <p class="text-xs text-gray-400">{{ $offer->contact_person ?: '-' }}{{ $offer->contact_position ? ' - ' . $offer->contact_position : '' }}</p>
                                <p class="text-xs text-gray-500">{{ $offer->contact_phone ?: '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $offer->employee?->name ?: 'Marketing tidak tersedia' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-900/50 text-blue-300 border border-blue-500/30">
                                    {{ ucfirst($offer->category) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-300 max-w-xs">
                                <p>{{ Str::limit($offer->offer_description ?: '-', 80) }}</p>
                                @if($offer->estimated_value)
                                    <p class="text-xs text-gray-500 mt-1">Rp {{ number_format((float) $offer->estimated_value, 0, ',', '.') }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $statusClasses[$offer->status] ?? 'bg-gray-700 text-gray-300' }}">
                                    {{ $offer->status_label }}
                                </span>
                                @if($offer->reason)
                                    <p class="text-xs text-gray-500 mt-1">{{ Str::limit($offer->reason, 45) }}</p>
                                @endif
                                @if($needsAccount)
                                    <p class="mt-2 text-xs text-amber-300 bg-amber-900/30 border border-amber-500/30 rounded px-2 py-1 w-fit">
                                        Perlu dibuatkan akun customer
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-300">
                                <p>Penawaran: {{ $offer->offer_date?->format('d/m/Y') ?? '-' }}</p>
                                <p class="text-xs text-gray-500">Follow up: {{ $offer->follow_up_date?->format('d/m/Y') ?? '-' }}</p>
                                <p class="text-xs text-gray-500">Meeting: {{ $offer->meeting_date?->format('d/m/Y H:i') ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($offer->project)
                                    <p class="font-medium text-white">{{ $offer->project->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $offer->project->divisions_count }} divisi, {{ $offer->project->tasks_count }} task</p>
                                    <p class="text-xs text-gray-500">Progress {{ $offer->project->progress ?? 0 }}%</p>
                                @else
                                    <span class="text-xs text-gray-500">Belum dibuat project</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($needsAccount)
                                    <button type="button"
                                            class="copy-offer-btn px-3 py-1 text-xs bg-emerald-600 hover:bg-emerald-700 text-white rounded transition"
                                            data-copy="{{ e($copyText) }}">
                                        Copy Data
                                    </button>
                                @else
                                    <span class="text-xs text-gray-500">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data penawaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $offers->appends(request()->query())->links() }}
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.copy-offer-btn').forEach(function (button) {
        button.addEventListener('click', async function () {
            const text = this.dataset.copy || '';
            const originalText = this.textContent;

            try {
                await navigator.clipboard.writeText(text);
            } catch (error) {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                textarea.remove();
            }

            this.textContent = 'Tersalin';
            setTimeout(() => this.textContent = originalText, 1500);
        });
    });
});
</script>
@endpush
@endsection
