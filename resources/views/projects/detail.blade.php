@extends('layouts.app')

@section('title', 'Detail Proyek: ' . $project->name)

@section('content')
<div class="p-6">
    {{-- HEADER --}}
    <div class="mb-6 flex justify-between items-start">
        <div>
            <a href="{{ route('projects.category.detail', ['category' => $project->category]) }}" 
               class="text-blue-400 hover:underline text-sm mb-2 inline-block">
               ← Kembali ke Daftar Proyek
            </a>
            <h1 class="text-2xl font-bold text-white">{{ $project->name }}</h1>
            <p class="text-gray-400">
                {{ $project->customer?->company ?? '-' }} 
                | {{ ucfirst($project->category) }}
                @if($project->deadline)
                | Deadline: {{ \Carbon\Carbon::parse($project->deadline)->format('d M Y') }}
                @endif
            </p>
        </div>
        <span class="px-3 py-1 rounded-full text-sm font-semibold
            @if($project->status === 'done') bg-green-500/20 text-green-400
            @elseif($project->status === 'ongoing') bg-blue-500/20 text-blue-400
            @else bg-gray-500/20 text-gray-400 @endif">
            {{ ucfirst($project->status) }}
        </span>
    </div>

    @isset($timelineData)
        <div class="mb-6">
            @include('components.project-milestone-timeline', ['timelineData' => $timelineData])
        </div>
    @endisset

    {{-- TASKLIST READ ONLY UNTUK ADMIN / DIREKTUR --}}
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-lg font-semibold text-white">Tasklist Proyek</h3>
                <p class="text-sm text-gray-400">Read-only untuk monitoring status, keterlambatan, dan laporan pengerjaan pegawai.</p>
            </div>
            <span class="text-xs text-gray-400">
                {{ $project->tasks->where('status', 'done')->count() }}/{{ $project->tasks->count() }} task selesai
            </span>
        </div>

        @if($project->tasks->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-700/60 text-gray-300 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Task</th>
                            <th class="px-4 py-3 text-left">Divisi</th>
                            <th class="px-4 py-3 text-left">Pegawai</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Deadline</th>
                            <th class="px-4 py-3 text-left">Laporan Pengerjaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($project->tasks->sortBy('deadline') as $task)
                            @php
                                $deadline = $task->deadline ? \Carbon\Carbon::parse($task->deadline)->startOfDay() : null;
                                $finishDate = $task->actual_end_date ?? $task->completed_at;
                                $finishDate = $finishDate ? \Carbon\Carbon::parse($finishDate)->startOfDay() : null;
                                $isLate = $deadline && (
                                    ($task->status !== 'done' && now()->startOfDay()->gt($deadline)) ||
                                    ($task->status === 'done' && $finishDate && $finishDate->gt($deadline))
                                );
                                $lateDays = $isLate
                                    ? (int) $deadline->diffInDays($finishDate ?? now()->startOfDay())
                                    : 0;
                            @endphp
                            <tr class="hover:bg-gray-700/30 {{ $isLate ? 'bg-red-900/10' : '' }}">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-white">{{ $task->title }}</p>
                                    @if($task->description)
                                        <p class="text-xs text-gray-500 mt-1">{{ Str::limit($task->description, 70) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-300">{{ $task->division?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <p class="text-gray-300">{{ $task->assignee?->name ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">{{ $task->assignee?->jabatan ?? '' }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $task->status_color }}">
                                        {{ $task->status_label }}
                                    </span>
                                    @if($isLate)
                                        <p class="text-xs text-red-400 mt-2">Terlambat {{ $lateDays }} hari</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-300">
                                    {{ $deadline ? $deadline->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($task->status === 'done')
                                        @if($task->completion_notes)
                                            <p class="text-gray-300">{{ Str::limit($task->completion_notes, 90) }}</p>
                                        @else
                                            <p class="text-gray-500">Tidak ada keterangan.</p>
                                        @endif
                                        @if($task->proof_image)
                                            <a href="{{ asset('storage/' . $task->proof_image) }}" target="_blank" class="inline-flex mt-2 text-xs text-blue-400 hover:underline">
                                                Lihat bukti foto
                                            </a>
                                        @else
                                            <p class="text-xs text-gray-500 mt-2">Bukti foto belum tersedia.</p>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-500">Belum ada laporan.</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-400 text-center py-6">Belum ada task untuk proyek ini.</p>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- KOLOM KIRI: PROGRESS DETAILS --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- PROGRESS BAR PER FASE --}}
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">📊 Progress Details</h3>
                
                @if($project->phases->count() > 0)
                <div class="space-y-4">
                    @foreach($project->phases as $phase)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-300">{{ $phase->display_name }}</span>
                            <span class="text-gray-400">{{ $phase->progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full transition-all duration-500
                                @if($phase->status === 'completed') bg-green-500
                                @elseif($phase->status === 'ongoing') bg-blue-500
                                @else bg-purple-500 @endif"
                                style="width: {{ $phase->progress }}%"></div>
                        </div>
                        {{-- SLA Info per Phase --}}
                        @if($phase->sla_status && $phase->sla_status !== 'on_track')
                        <p class="text-[10px] mt-1 {{ $phase->sla_status_color }}">
                            ⚠️ SLA: {{ $phase->sla_status_label }}
                        </p>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-400 text-center py-4">
                    Data fase proyek belum tersedia.
                </p>
                @endif
                
                {{-- Overall Progress --}}
                <div class="mt-6 pt-4 border-t border-gray-700">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-white font-semibold">Overall Progress</span>
                        <span class="text-blue-400 font-bold">{{ $overallProgress }}%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 rounded-full" 
                             style="width: {{ $overallProgress }}%"></div>
                    </div>
                </div>
            </div>

            {{-- PROJECT TIMELINE VISUAL --}}
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-6">🚀 Project Timeline</h3>
                
                @if($project->phases->count() > 0)
                <div class="relative">
                    {{-- Garis Penghubung --}}
                    <div class="absolute top-5 left-0 w-full h-1 bg-gray-700 rounded"></div>
                    <div class="absolute top-5 left-0 h-1 bg-gradient-to-r from-green-500 via-blue-500 to-gray-600 rounded transition-all" 
                         style="width: {{ $overallProgress }}%"></div>
                    
                    {{-- Langkah-langkah --}}
                    <div class="relative grid grid-cols-5 gap-2">
                        @foreach($project->phases as $phase)
                        <div class="text-center">
                            {{-- Icon --}}
                            <div class="w-10 h-10 mx-auto rounded-full flex items-center justify-center mb-2 border-2 z-10 relative
                                @if($phase->status === 'completed') bg-green-500 border-green-500 text-white
                                @elseif($phase->status === 'ongoing') bg-blue-500 border-blue-500 text-white animate-pulse
                                @else bg-gray-700 border-gray-600 text-gray-400 @endif">
                                @if($phase->status === 'completed')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @elseif($phase->status === 'ongoing')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @else
                                    <span class="text-xs">{{ $phase->phase_order }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 font-medium">{{ Str::limit($phase->display_name, 16) }}</p>
                            <p class="text-[10px] mt-1
                                @if($phase->status === 'completed') text-green-400
                                @elseif($phase->status === 'ongoing') text-blue-400
                                @else text-gray-500 @endif">
                                {{ ucfirst($phase->status) }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <p class="text-gray-400 text-center py-4">
                    Timeline belum tersedia.
                </p>
                @endif
            </div>
        </div>

        {{-- KOLOM KANAN: SLA & INFO --}}
        <div class="space-y-6">
            
            {{-- SLA STATUS --}}
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">⏱️ SLA Status</h3>
                
                @if($project->phases->count() > 0)
                <div class="space-y-3 mb-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-sm">On Track</span>
                        <span class="text-green-400 font-bold">{{ $slaSummary['on_track'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-sm">Warning</span>
                        <span class="text-yellow-400 font-bold">{{ $slaSummary['warning'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400 text-sm">Breached</span>
                        <span class="text-red-400 font-bold">{{ $slaSummary['breached'] }}</span>
                    </div>
                </div>

                <div class="p-3 rounded-lg border
                    @if($slaStatus === 'on_track') bg-green-500/10 border-green-500/30 text-green-400
                    @elseif($slaStatus === 'warning') bg-yellow-500/10 border-yellow-500/30 text-yellow-400
                    @else bg-red-500/10 border-red-500/30 text-red-400 @endif">
                    <p class="text-sm font-semibold">Overall SLA: {{ ucfirst($slaStatus) }}</p>
                </div>
                @else
                <p class="text-gray-400 text-sm">
                    Data SLA belum tersedia.
                </p>
                @endif
            </div>

            {{-- PROGRESS TIMELINE LIST --}}
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">📅 Progress Timeline</h3>
                
                @if($project->phases->count() > 0)
                <div class="space-y-3">
                    @foreach($project->phases as $phase)
                    <div class="flex items-center justify-between p-3 rounded-lg 
                        @if($phase->status === 'ongoing') bg-blue-500/10 border border-blue-500/30
                        @else bg-gray-700/50 @endif">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full
                                @if($phase->status === 'completed') bg-green-500
                                @elseif($phase->status === 'ongoing') bg-blue-500 animate-pulse
                                @else bg-gray-500 @endif"></div>
                            <div>
                                <p class="text-sm font-medium text-white">{{ $phase->display_name }}</p>
                                <p class="text-xs text-gray-400">
                                    @if($phase->target_date)
                                        Target: {{ $phase->target_date->format('d M') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs px-2 py-1 rounded
                                @if($phase->status === 'completed') bg-green-500/20 text-green-400
                                @elseif($phase->status === 'ongoing') bg-blue-500/20 text-blue-400
                                @else bg-gray-500/20 text-gray-400 @endif">
                                {{ $phase->status === 'completed' ? 'Selesai' : ($phase->status === 'ongoing' ? 'Berjalan' : 'Menunggu') }}
                            </span>
                            @if($phase->sla_status === 'breached')
                            <p class="text-[10px] text-red-400 mt-1">SLA Breached!</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-400 text-center py-4">
                    Timeline belum tersedia.
                </p>
                @endif
            </div>

            {{-- PROJECT INFO --}}
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">ℹ️ Info Proyek</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Kategori</span>
                        <span class="text-white">{{ ucfirst($project->category) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Customer</span>
                        <span class="text-white">{{ $project->customer?->company ?? '-' }}</span>
                    </div>
                    @if($project->start_date)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Mulai</span>
                        <span class="text-white">{{ $project->start_date->format('d M Y') }}</span>
                    </div>
                    @endif
                    @if($project->deadline)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Deadline</span>
                        <span class="text-white">{{ $project->deadline->format('d M Y') }}</span>
                    </div>
                    @endif
                    @if($project->sla)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Target SLA</span>
                        <span class="text-white">{{ $project->sla }}%</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
