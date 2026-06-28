@extends('layouts.app')

@section('title', 'Manage Projects')

@section('content')
<div class="p-6">
    {{-- HEADER --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">📦 Manage Projects</h1>
            <p class="text-gray-400">
                @if(Auth::user()->role->name === 'direktur')
                    Monitoring semua proyek (Read-Only)
                @else
                    Kelola semua proyek dan assign pekerjaan
                @endif
            </p>
        </div>
        {{-- ✅ Tombol Tambah Proyek hanya untuk Super Admin --}}
        @if(Auth::user()->role->name !== 'direktur')
        <a href="{{ route('admin.projects.create') }}" 
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Proyek
        </a>
        @endif
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <p class="text-gray-400 text-sm">Total Proyek</p>
            <p class="text-2xl font-bold text-white">{{ $projects->total() }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <p class="text-gray-400 text-sm">Ongoing</p>
            <p class="text-2xl font-bold text-yellow-500">{{ $projects->where('status', 'ongoing')->count() }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <p class="text-gray-400 text-sm">Completed</p>
            <p class="text-2xl font-bold text-green-500">{{ $projects->where('status', 'done')->count() }}</p>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Nama Proyek</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Customer</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Kategori</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Deadline</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Progress</th>
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Task</th>
                    
                    {{-- ✅ KOLOM AKSI: HANYA MUNCUL JIKA BUKAN DIREKTUR --}}
                    @if(Auth::user()->role->name !== 'direktur')
                    <th class="px-4 py-3 text-left text-xs text-gray-300 uppercase">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($projects as $project)
                <tr class="hover:bg-gray-700/50 transition">
                    <td class="px-4 py-3">
                        <p class="font-medium text-white">{{ $project->name }}</p>
                        <p class="text-xs text-gray-400">{{ $project->client_name }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300">
                        {{ $project->customer?->company ?? $project->client_name ?? '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-900/50 text-blue-300 border border-blue-500/30">
                            {{ ucfirst($project->category) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($project->status === 'done') bg-green-900/50 text-green-300 border border-green-500/30
                            @elseif($project->status === 'ongoing') bg-blue-900/50 text-blue-300 border border-blue-500/30
                            @else bg-gray-700 text-gray-300 @endif">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300">
                        @if($project->deadline)
                            {{ \Carbon\Carbon::parse($project->deadline)->format('d/m/Y') }}
                            @if(\Carbon\Carbon::parse($project->deadline)->isPast() && $project->status !== 'done')
                                <p class="text-xs text-red-400">Terlambat!</p>
                            @endif
                        @else - @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="w-full bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $project->progress ?? 0 }}%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">{{ $project->progress ?? 0 }}%</p>
                    </td>
                    
                    {{-- ✅ KOLOM TASK: Query langsung tanpa join (FIX ERROR) --}}
                    <td class="px-4 py-3">
                        @php
                            $taskCount = \App\Models\ProjectTask::where('project_id', $project->id)->count();
                            $completedTasks = \App\Models\ProjectTask::where('project_id', $project->id)
                                ->where('status', 'done')
                                ->count();
                        @endphp
                        @if($taskCount > 0)
                            <div class="text-center">
                                <span class="px-2 py-1 text-xs rounded-full bg-indigo-900/50 text-indigo-300 border border-indigo-500/30">
                                    {{ $completedTasks }}/{{ $taskCount }}
                                </span>
                                <p class="text-xs text-gray-400 mt-1">{{ round(($completedTasks / $taskCount) * 100) }}%</p>
                            </div>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-red-900/30 text-red-400 border border-red-500/30">
                                Belum ada
                            </span>
                        @endif
                    </td>
                    
                    {{-- ✅ TOMBOL AKSI: HANYA MUNCUL JIKA BUKAN DIREKTUR --}}
                    @if(Auth::user()->role->name !== 'direktur')
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.tasks.index.by.project', $project->id) }}" 
                               class="px-3 py-1 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded transition flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Kelola Task
                            </a>
                            <a href="{{ route('admin.projects.edit', $project) }}" 
                               class="px-3 py-1 text-xs bg-yellow-500 hover:bg-yellow-600 text-white rounded transition">
                                Edit
                            </a>
                            <form action="{{ route('admin.projects.destroy', $project) }}" method="POST" 
                                  onsubmit="return confirm('Yakin hapus proyek ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="px-3 py-1 text-xs bg-red-500 hover:bg-red-600 text-white rounded transition">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    {{-- ✅ Colspan menyesuaikan apakah kolom aksi ada atau tidak --}}
                    <td colspan="{{ Auth::user()->role->name !== 'direktur' ? 8 : 7 }}" class="px-4 py-8 text-center text-gray-400">
                        Belum ada data proyek. 
                        @if(Auth::user()->role->name !== 'direktur')
                            <a href="{{ route('admin.projects.create') }}" class="text-blue-400 hover:underline">Tambahkan proyek pertama</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $projects->links() }}
    </div>
</div>
@endsection
