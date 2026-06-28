@extends('layouts.app')

@section('title', 'Kelola Task - ' . $project->name)

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">📋 Task List: {{ $project->name }}</h1>
            <p class="text-gray-400 text-sm">Kategori: {{ ucfirst($project->category) }} | Deadline: {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d/m/Y') : '-' }}</p>
        </div>
        <a href="{{ route('admin.tasks.create', ['project_id' => $project->id]) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Task
        </a>
    </div>

    {{-- TAMPILAN PER DIVISI --}}
    @if($project->divisions->count() > 0)
        @foreach($project->divisions as $division)
        <div class="bg-gray-800 rounded-lg border border-gray-700 mb-6">
            <div class="p-4 border-b border-gray-700 bg-gray-700/30 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-sm">
                        {{ $loop->iteration }}
                    </span>
                    DIVISI: {{ strtoupper($division->name) }}
                </h3>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-400">
                        {{ $division->tasks->count() }} Task
                    </span>
                    <a href="{{ route('admin.tasks.create', ['project_id' => $project->id, 'division_id' => $division->id]) }}" 
                       class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs rounded transition flex items-center gap-1 font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Task
                    </a>
                </div>
            </div>
            
            @if($division->tasks->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-700/50 text-gray-300 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Judul Task</th>
                            <th class="px-4 py-3 text-left">Ditugaskan Kepada</th>
                            <th class="px-4 py-3 text-left">Deadline</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">SLA</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($division->tasks as $task)
                        <tr class="hover:bg-gray-700/30 transition">
                            <td class="px-4 py-3">
                                <p class="font-medium text-white">{{ $task->title }}</p>
                                @if($task->description)
                                    <p class="text-xs text-gray-500 mt-1">{{ Str::limit($task->description, 50) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-300">
                                {{ $task->assignee->name ?? '-' }}
                                <p class="text-xs text-gray-500">{{ $task->assignee->jabatan ?? '' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($task->deadline)
                                    <p class="text-gray-300">{{ \Carbon\Carbon::parse($task->deadline)->format('d/m/Y') }}</p>
                                    @if($task->isOverdue())
                                        <p class="text-xs text-red-400">Terlambat!</p>
                                    @elseif($task->isDueSoon())
                                        <p class="text-xs text-orange-400">H-{{ $task->days_until_deadline }}</p>
                                    @endif
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $task->status_color }}">
                                    {{ $task->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-300">
                                {{ $task->sla_target ?? 100 }}%
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ route('admin.tasks.edit', $task) }}" class="px-3 py-1 text-xs bg-yellow-600 hover:bg-yellow-700 text-white rounded transition">Edit</a>
                                    <form action="{{ route('admin.tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Yakin hapus task ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-3 py-1 text-xs bg-red-600 hover:bg-red-700 text-white rounded transition">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-6 text-center text-gray-500">
                Belum ada task untuk divisi ini. 
                <a href="{{ route('admin.tasks.create', ['project_id' => $project->id, 'division_id' => $division->id]) }}" class="text-blue-400 hover:underline">Tambah task sekarang</a>
            </div>
            @endif
        </div>
        @endforeach
    @else
        <div class="bg-yellow-900/20 border border-yellow-500/30 rounded-lg p-6 text-center">
            <p class="text-yellow-300 text-lg mb-2">⚠️ Belum ada divisi yang dipilih untuk proyek ini</p>
            <p class="text-gray-400 text-sm">Silakan edit proyek terlebih dahulu untuk memilih divisi yang terlibat</p>
            <a href="{{ route('admin.projects.edit', $project) }}" class="inline-block mt-4 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                Edit Proyek
            </a>
        </div>
    @endif
</div>
@endsection