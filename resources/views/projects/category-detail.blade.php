@extends('layouts.app')

@section('title', 'Detail Bidang ' . ucfirst($category))

@section('content')
<div class="p-6">
    {{-- HEADER --}}
    <div class="mb-6 flex justify-between items-center flex-wrap gap-4">
        <div>
            @php
                $userRole = Auth::user()?->role?->name ?? '';
                $backRoute = match($userRole) {
                    'super_admin' => 'admin.dashboard',
                    'direktur' => 'direktur.dashboard',
                    'pegawai' => 'employee.dashboard',
                    'customer' => 'customer.dashboard',
                    default => 'main.dashboard',
                };
            @endphp
            <a href="{{ route($backRoute) }}" class="text-blue-600 hover:underline mb-2 inline-block">← Kembali ke Dashboard</a>
            <h1 class="text-2xl font-bold text-white">📊 Detail Bidang: {{ $labels[$category] ?? ucfirst($category) }}</h1>
        </div>
        
        {{-- ✅ TOMBOL EXPORT EXCEL --}}
        <a href="{{ route('projects.export', ['category' => $category]) }}" 
           class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Export Excel
        </a>
    </div>
    
    {{-- Statistik --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-800 p-4 rounded border border-gray-700">
            <p class="text-gray-400 text-xs">Total</p>
            <p class="text-xl font-bold text-white">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 p-4 rounded border border-gray-700">
            <p class="text-gray-400 text-xs">Ongoing</p>
            <p class="text-xl font-bold text-blue-500">{{ $stats['ongoing'] ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 p-4 rounded border border-gray-700">
            <p class="text-gray-400 text-xs">Selesai</p>
            <p class="text-xl font-bold text-green-500">{{ $stats['done'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Daftar Proyek --}}
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <h3 class="font-semibold mb-4 text-white">Daftar Proyek {{ $labels[$category] ?? ucfirst($category) }}</h3>
        @if(isset($projects) && $projects->count() > 0)
        <div class="space-y-3">
            @foreach($projects as $p)
            <div class="flex justify-between items-center p-3 border border-gray-600 rounded hover:bg-gray-700/50 transition">
                <div class="flex items-center gap-4">
                    <a href="{{ route('projects.detail', $p->id) }}" 
                       class="font-medium text-blue-400 hover:text-blue-300 hover:underline transition">
                        {{ $p->name }}
                    </a>
                    
                    @if(in_array($userRole, ['super_admin', 'direktur']))
                        @php
                            $slaStatus = 'on_track';
                            if($p->deadline) {
                                $daysLeft = now()->diffInDays($p->deadline, false);
                                if($daysLeft < 0 && $p->status !== 'done') {
                                    $slaStatus = 'breached';
                                } elseif($daysLeft <= 3 && $p->status !== 'done') {
                                    $slaStatus = 'warning';
                                }
                            }
                            $slaColors = [
                                'on_track' => 'bg-green-500/20 text-green-400 border-green-500/30',
                                'warning' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
                                'breached' => 'bg-red-500/20 text-red-400 border-red-500/30',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 text-[10px] rounded border {{ $slaColors[$slaStatus] }}" title="SLA Status">
                            SLA: {{ ucfirst($slaStatus) }}
                        </span>
                    @endif
                </div>
                
                <div class="flex items-center gap-3">
                    <p class="text-xs text-gray-400 hidden md:block">{{ $p->customer?->company ?? '-' }}</p>
                    <span class="px-2 py-1 text-xs rounded
                        @if($p->status === 'done') bg-green-500/20 text-green-400
                        @elseif($p->status === 'ongoing') bg-blue-500/20 text-blue-400
                        @else bg-gray-500/20 text-gray-400 @endif">
                        {{ ucfirst($p->status) }}
                    </span>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-400 text-center py-4">Tidak ada proyek untuk kategori ini.</p>
        @endif
    </div>
</div>
@endsection
