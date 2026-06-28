@extends('layouts.app')

@section('title', 'Portal Customer - ' . (Auth::user()?->company ?? 'Customer'))

@section('content')
<div class="p-6">
    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-white mb-2">
            🏢 Halo, {{ Auth::user()?->company ?? Auth::user()?->name }}!
        </h1>
        <p class="text-gray-400">
            Portal Customer - Pantau Progress Proyek Anda
        </p>
    </div>

    {{-- STATISTIK CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <p class="text-gray-400 text-sm mb-1">Total Proyek</p>
            <p class="text-3xl font-bold text-blue-500">{{ $totalProjects ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <p class="text-gray-400 text-sm mb-1">Sedang Berjalan</p>
            <p class="text-3xl font-bold text-yellow-500">{{ $ongoingProjects ?? 0 }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <p class="text-gray-400 text-sm mb-1">Selesai</p>
            <p class="text-3xl font-bold text-green-500">{{ $completedProjects ?? 0 }}</p>
        </div>
    </div>

    {{-- DAFTAR PROYEK --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-700">
            <h2 class="text-xl font-bold text-white">Daftar Proyek Anda</h2>
        </div>

        <div class="p-6">
            @if(isset($projects) && $projects->count() > 0)
                <div class="space-y-6">
                    @foreach($projects as $project)
                    <div class="bg-gray-700/50 rounded-lg p-5 border border-gray-600 hover:border-blue-500 transition">
                        {{-- Header Proyek --}}
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-white mb-1">{{ $project->name }}</h3>
                                <p class="text-gray-400 text-sm">{{ $project->client_name ?? '-' }}</p>
                            </div>
                            <span class="px-3 py-1 text-xs rounded-full font-semibold
                                @if($project->status === 'done') bg-green-500/20 text-green-400
                                @elseif($project->status === 'ongoing') bg-blue-500/20 text-blue-400
                                @else bg-gray-500/20 text-gray-400 @endif">
                                {{ ucfirst($project->status) }}
                            </span>
                        </div>

                        {{-- Progress Bar --}}
                        @if($project->status === 'ongoing')
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-400">Progress</span>
                                <span class="text-blue-400 font-semibold">{{ $project->progress }}%</span>
                            </div>
                            <div class="w-full bg-gray-600 rounded-full h-3">
                                <div class="bg-blue-500 h-3 rounded-full transition-all" style="width: {{ $project->progress }}%"></div>
                            </div>
                        </div>
                        @endif

                        {{-- Timeline --}}
                        <div class="grid grid-cols-2 gap-4 text-sm pt-4 border-t border-gray-600">
                            <div>
                                <p class="text-gray-400 text-xs mb-1">Tanggal Mulai</p>
                                <p class="text-gray-300 font-medium">
                                    {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') : '-' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-400 text-xs mb-1">Deadline</p>
                                <p class="text-gray-300 font-medium">
                                    {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d/m/Y') : '-' }}
                                </p>
                            </div>
                        </div>

                        {{-- Progress Per Divisi --}}
                        @if($project->divisions && $project->divisions->count() > 0)
                        <div class="mt-4 pt-4 border-t border-gray-600">
                            <p class="text-gray-400 text-sm mb-3">PROGRESS PER DIVISI</p>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($project->divisions as $division)
                                <div class="bg-gray-600/50 rounded p-3">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-white text-sm font-medium">{{ $division->name }}</span>
                                        <span class="text-blue-400 text-xs">
                                            {{ $division->tasks->where('status', 'done')->count() }}/{{ $division->tasks->count() }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-700 rounded-full h-1.5">
                                        @php
                                            $divProgress = $division->tasks->count() > 0 
                                                ? ($division->tasks->where('status', 'done')->count() / $division->tasks->count()) * 100 
                                                : 0;
                                        @endphp
                                        <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $divProgress }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-16">
                    <svg class="w-20 h-20 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-gray-400 text-lg mb-2">Belum ada proyek untuk perusahaan Anda</p>
                    <p class="text-gray-500 text-sm">Hubungi tim marketing untuk informasi lebih lanjut</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection