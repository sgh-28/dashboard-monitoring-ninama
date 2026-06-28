@extends('layouts.app')

@section('title', 'Proyek CCTV')

@section('content')
<div class="p-6">
    <!-- Header dengan 5 Statistik Cards (Clickable untuk Modal) -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Dashboard CCTV</h2>

        <!-- 5 Statistik Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Penawaran Marketing (Clickable → Modal) -->
            <div onclick="openModal('offers')" 
                 class="hidden bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 cursor-pointer hover:shadow-lg hover:border-blue-500 transition group">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Penawaran Marketing</p>
                <p class="text-2xl font-bold text-blue-600 group-hover:text-blue-500">{{ $stats['offer'] ?? 0 }}</p>
                <div class="mt-2 flex justify-end opacity-0 group-hover:opacity-100 transition">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
            </div>

            <!-- Progres Penawaran (Clickable → Modal) -->
            <div onclick="openModal('progress_offers')" 
                 class="hidden bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 cursor-pointer hover:shadow-lg hover:border-yellow-500 transition group">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Progres Penawaran</p>
                <p class="text-2xl font-bold text-yellow-600 group-hover:text-yellow-500">{{ $stats['progress_offer'] ?? 0 }}</p>
                <div class="mt-2 flex justify-end opacity-0 group-hover:opacity-100 transition">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Penawaran Ditolak (Clickable → Modal) -->
            <div onclick="openModal('rejected')" 
                 class="hidden bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 cursor-pointer hover:shadow-lg hover:border-red-500 transition group">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Penawaran Ditolak</p>
                <p class="text-2xl font-bold text-red-600 group-hover:text-red-500">{{ $stats['rejected'] ?? 0 }}</p>
                <div class="mt-2 flex justify-end opacity-0 group-hover:opacity-100 transition">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Proyek On-Going (Static) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Proyek On-Going</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['ongoing'] ?? 0 }}</p>
                <div class="mt-2 flex justify-end">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>

            <!-- Proyek Selesai (Static) -->
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Proyek Selesai</p>
                <p class="text-2xl font-bold text-purple-600">{{ $stats['completed'] ?? 0 }}</p>
                <div class="mt-2 flex justify-end">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Proyek On-Going dengan Task Breakdown -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 mb-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Daftar Proyek On-Going</h3>
        
        @if(isset($ongoingProjects) && $ongoingProjects->count() > 0)
            <div class="space-y-4">
                @foreach($ongoingProjects as $project)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-400 transition">
                    <!-- Project Header -->
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h4 class="font-semibold text-gray-800 dark:text-gray-200">{{ $project->name }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->client_name ?? '-' }}</p>
                        </div>
                        <span class="text-sm font-bold text-blue-600 bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-full">{{ $project->progress }}%</span>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 mb-4">
                        <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-500" style="width: {{ $project->progress }}%"></div>
                    </div>

                    <!-- Task Breakdown per Divisi -->
                    @if($project->divisions && $project->divisions->count() > 0)
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Progress per Divisi</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($project->divisions as $division)
                            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3 border border-gray-100 dark:border-gray-600">
                                <div class="flex justify-between items-center mb-2">
                                    <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $division->name }}</h5>
                                    <span class="text-xs px-2 py-0.5 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 rounded">
                                        {{ $division->tasks->where('status', 'done')->count() }}/{{ $division->tasks->count() }}
                                    </span>
                                </div>
                                <div class="space-y-2">
                                    @foreach($division->tasks as $task)
                                    <div class="flex items-center gap-2 text-xs">
                                        @if($task->status === 'done')
                                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="text-gray-500 dark:text-gray-400 line-through flex-1">{{ $task->name }}</span>
                                        @elseif($task->status === 'in_progress')
                                            <svg class="w-4 h-4 text-yellow-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-gray-700 dark:text-gray-300 font-medium flex-1">{{ $task->name }}</span>
                                        @else
                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-gray-500 dark:text-gray-400 flex-1">{{ $task->name }}</span>
                                        @endif
                                        @if($task->deadline)
                                            <span class="text-gray-400 dark:text-gray-500 text-[10px] whitespace-nowrap">
                                                {{ \Carbon\Carbon::parse($task->deadline)->format('d/m') }}
                                            </span>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Timeline Info -->
                    <div class="grid grid-cols-3 gap-4 text-sm pt-3 border-t border-gray-100 dark:border-gray-700">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-xs">Tgl Mulai</p>
                            <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-xs">Tgl Selesai</p>
                            <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-xs">Deadline</p>
                            <p class="text-gray-800 dark:text-gray-200 font-medium {{ $project->deadline && \Carbon\Carbon::parse($project->deadline)->isPast() && $project->status !== 'done' ? 'text-red-500' : '' }}">
                                {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-10 text-gray-500 dark:text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <p>Tidak ada proyek on-going untuk kategori ini.</p>
            </div>
        @endif
    </div>

    <!-- Daftar Proyek Selesai -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Daftar Proyek Selesai</h3>
        
        @if(isset($completedProjects) && $completedProjects->count() > 0)
            <div class="space-y-4">
                @foreach($completedProjects as $project)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-green-400 transition">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="font-semibold text-gray-800 dark:text-gray-200">{{ $project->name }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->client_name ?? '-' }}</p>
                        </div>
                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300 text-sm rounded-full font-semibold">SLA: {{ $project->sla ?? '100' }}%</span>
                    </div>
                    <div class="grid grid-cols-3 gap-4 text-sm mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-xs">Tgl Mulai</p>
                            <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-xs">Tgl Selesai</p>
                            <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $project->end_date ? \Carbon\Carbon::parse($project->end_date)->format('d/m/Y') : '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-xs">Deadline</p>
                            <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d/m/Y') : '-' }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-10 text-gray-500 dark:text-gray-400">
                <p>Belum ada proyek selesai.</p>
            </div>
        @endif
    </div>
</div>

<!-- ================= MODALS ================= -->
<x-statistic-modal title="Detail Penawaran Marketing" :projects="$offerProjects ?? collect()" type="offers" />
<x-statistic-modal title="Detail Progres Penawaran" :projects="$progressOfferProjects ?? collect()" type="progress_offers" />
<x-statistic-modal title="Detail Penawaran Ditolak" :projects="$rejectedProjects ?? collect()" type="rejected" />

@endsection

@push('scripts')
<script>
    window.openModal = function(type) {
        const modal = document.getElementById('modal-' + type);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    };
    window.closeModal = function(type) {
        const modal = document.getElementById('modal-' + type);
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    };
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[id^="modal-"]').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    const type = this.id.replace('modal-', '');
                    closeModal(type);
                }
            });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('[id^="modal-"]:not(.hidden)').forEach(modal => {
                    const type = modal.id.replace('modal-', '');
                    closeModal(type);
                });
            }
        });
    });
</script>
@endpush
