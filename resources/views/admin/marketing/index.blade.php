@extends('layouts.app')

@section('title', 'Laporan Marketing - Admin')

@section('content')
<div class="p-6">
    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">
            📊 Laporan Penawaran Marketing
        </h1>
        <p class="text-gray-400">Monitor kinerja tim marketing per bidang</p>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <p class="text-gray-400 text-sm">Total Penawaran</p>
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <p class="text-gray-400 text-sm">Deal / Closing</p>
            <p class="text-2xl font-bold text-green-500">{{ $stats['deal'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <p class="text-gray-400 text-sm">Pending</p>
            <p class="text-2xl font-bold text-yellow-500">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <p class="text-gray-400 text-sm">Ditolak</p>
            <p class="text-2xl font-bold text-red-500">{{ $stats['rejected'] }}</p>
        </div>
    </div>

    {{-- FILTERS --}}
    <form method="GET" class="bg-gray-800 rounded-lg p-4 border border-gray-700 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Bidang</label>
                <select name="category" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white">
                    <option value="">Semua Bidang</option>
                    <option value="web" {{ request('category') == 'web' ? 'selected' : '' }}>Web & Aplikasi</option>
                    <option value="internet" {{ request('category') == 'internet' ? 'selected' : '' }}>Internet</option>
                    <option value="cctv" {{ request('category') == 'cctv' ? 'selected' : '' }}>CCTV</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white">
                    <option value="">Semua Status</option>
                    <option value="deal" {{ request('status') == 'deal' ? 'selected' : '' }}>Deal</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Cari</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama perusahaan..." 
                       class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded">
                    🔍 Filter
                </button>
            </div>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Perusahaan</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Bidang</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Tanggal</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($offers as $offer)
                <tr class="hover:bg-gray-700/50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-white">{{ $offer->company_name }}</p>
                        <p class="text-xs text-gray-400">{{ Str::limit($offer->company_address, 25) }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded bg-blue-900 text-blue-300">{{ ucfirst($offer->category) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $statusColors = [
                                'penawaran' => 'bg-blue-900 text-blue-300',
                                'follow_up' => 'bg-yellow-900 text-yellow-300',
                                'meeting' => 'bg-purple-900 text-purple-300',
                                'menunggu_keputusan' => 'bg-gray-700 text-gray-300',
                                'negosiasi' => 'bg-orange-900 text-orange-300',
                                'deal' => 'bg-green-900 text-green-300',
                                'pending' => 'bg-red-900 text-red-300',
                                'rejected' => 'bg-red-900 text-red-300',
                                'no_response' => 'bg-gray-700 text-gray-300',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs rounded {{ $statusColors[$offer->status] ?? 'bg-gray-700 text-gray-300' }}">
                            {{ $offer->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        {{ \Carbon\Carbon::parse($offer->offer_date)->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300">
                        @if($offer->reason)
                            <p class="text-gray-300">{{ Str::limit($offer->reason, 50) }}</p>
                        @elseif($offer->notes)
                            <p class="text-gray-400 italic">{{ Str::limit($offer->notes, 50) }}</p>
                        @else
                            <p class="text-gray-500 italic">-</p>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                        Tidak ada data penawaran.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $offers->links() }}
    </div>
</div>
@endsection