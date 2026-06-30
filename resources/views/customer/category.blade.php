@extends('layouts.customer')

@section('title', 'Proyek ' . ucfirst($category) . ' - Portal Customer')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
            @if($category === 'web') Web & Aplikasi
            @elseif($category === 'internet') Layanan Internet
            @else CCTV
            @endif
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Daftar Proyek {{ ucfirst($category) }}</p>
    </div>

    <!-- Statistik Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Proyek</p>
            <p class="text-2xl font-bold text-blue-600">{{ $totalProjects ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Sedang Berjalan</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $ongoingProjects ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Selesai</p>
            <p class="text-2xl font-bold text-green-600">{{ $completedProjects ?? 0 }}</p>
        </div>
    </div>

    <!-- Projects List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Daftar Proyek</h3>
        
        @if($projects->count() > 0)
            <div class="space-y-4">
                @foreach($projects as $project)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-blue-400 transition">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h4 class="font-semibold text-gray-800 dark:text-gray-200">{{ $project->name }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $project->client_name ?? '-' }}</p>
                        </div>
                        <span class="px-3 py-1 text-xs rounded-full font-semibold
                            @if($project->status === 'done') bg-green-100 text-green-700
                            @elseif($project->status === 'ongoing') bg-blue-100 text-blue-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>
                    
                    @if($project->status === 'ongoing')
                    <div class="mt-3">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-600 dark:text-gray-400">Progress</span>
                            <span class="font-semibold text-blue-600">{{ $project->progress }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: {{ $project->progress }}%"></div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-2 gap-4 text-sm mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-xs">Mulai</p>
                            <p class="text-gray-800 dark:text-gray-200">
                                {{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400 text-xs">Deadline</p>
                            <p class="text-gray-800 dark:text-gray-200">
                                {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                    </div>

                    @if($project->divisions && $project->divisions->flatMap->tasks->count() > 0)
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <p class="text-gray-500 dark:text-gray-400 text-xs uppercase mb-3">Tasklist Proyek</p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-left text-xs text-gray-500 dark:text-gray-400 uppercase border-b border-gray-100 dark:border-gray-700">
                                    <tr>
                                        <th class="pb-2 font-medium">Task</th>
                                        <th class="pb-2 font-medium">Divisi</th>
                                        <th class="pb-2 font-medium">Status</th>
                                        <th class="pb-2 font-medium">Deadline</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @foreach($project->divisions->flatMap->tasks->sortBy('deadline') as $task)
                                    <tr>
                                        <td class="py-2 pr-3 text-gray-800 dark:text-gray-200">{{ $task->title }}</td>
                                        <td class="py-2 pr-3 text-gray-500 dark:text-gray-400">{{ $task->division?->name ?? '-' }}</td>
                                        <td class="py-2 pr-3">
                                            <span class="px-2 py-1 text-xs rounded-full {{ $task->status_color }}">
                                                {{ $task->status_label }}
                                            </span>
                                        </td>
                                        <td class="py-2 text-gray-500 dark:text-gray-400">
                                            {{ $task->deadline ? \Carbon\Carbon::parse($task->deadline)->format('d/m/Y') : '-' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <p>Belum ada proyek di kategori ini.</p>
            </div>
        @endif
    </div>
</div>
@endsection
