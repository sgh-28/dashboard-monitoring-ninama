@extends('layouts.app')

@section('title', 'Daftar Tugas Saya')

@section('content')
<div class="p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">📋 Daftar Tugas Saya</h1>
            <p class="text-gray-400 text-sm">Semua tugas yang ditugaskan kepada Anda</p>
        </div>
    </div>

    @if(isset($managedProjects) && $managedProjects->count() > 0)
        <div class="bg-gray-800 rounded-lg border border-gray-700 p-5 mb-6">
            <div class="flex justify-between items-start gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-white">Verifikasi Project Management</h2>
                    <p class="text-sm text-gray-400">Tandai proyek selesai setelah seluruh task pegawai selesai dan sudah dicek.</p>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($managedProjects as $project)
                    @php
                        $totalTasks = $project->tasks_count;
                        $completedTasks = $project->completed_tasks_count;
                        $allTasksDone = $totalTasks > 0 && $completedTasks === $totalTasks;
                        $projectProgress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                    @endphp

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-700/40 border border-gray-600 rounded-lg p-4">
                        <div class="flex-1">
                            <p class="font-medium text-white">{{ $project->name }}</p>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ $completedTasks }}/{{ $totalTasks }} task selesai
                                <span class="mx-2">|</span>
                                Status: {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </p>
                            <div class="mt-3 flex items-center gap-2 max-w-sm">
                                <div class="flex-1 bg-gray-800 rounded-full h-2">
                                    <div class="h-2 rounded-full {{ $allTasksDone ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ $projectProgress }}%"></div>
                                </div>
                                <span class="text-xs text-gray-300">{{ $projectProgress }}%</span>
                            </div>
                        </div>

                        <form action="{{ route('employee.tasks.projects.complete', $project) }}" method="POST" onsubmit="return confirm('Tandai proyek ini selesai? Pastikan semua task sudah dicek.')" class="shrink-0">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 text-sm rounded-lg transition font-medium {{ $allTasksDone ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-600 text-gray-300 cursor-not-allowed' }}"
                                    {{ $allTasksDone ? '' : 'disabled' }}>
                                Tandai Proyek Selesai
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- FILTER TABS --}}
    @php
        $currentStatus = request('status');
    @endphp
    <div class="flex gap-2 mb-6 flex-wrap">
        <a href="{{ route('employee.tasks.index') }}" 
           class="px-4 py-2 text-sm rounded-lg transition {{ !$currentStatus ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
            Semua
        </a>
        <a href="{{ route('employee.tasks.index', ['status' => 'pending']) }}" 
           class="px-4 py-2 text-sm rounded-lg transition {{ $currentStatus === 'pending' ? 'bg-gray-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
            ⏳ Pending
        </a>
        <a href="{{ route('employee.tasks.index', ['status' => 'ongoing']) }}" 
           class="px-4 py-2 text-sm rounded-lg transition {{ $currentStatus === 'ongoing' ? 'bg-yellow-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
            🔄 In Progress
        </a>
        <a href="{{ route('employee.tasks.index', ['status' => 'done']) }}" 
           class="px-4 py-2 text-sm rounded-lg transition {{ $currentStatus === 'done' ? 'bg-green-600 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600' }}">
            ✅ Selesai
        </a>
    </div>

    {{-- TABEL TUGAS --}}
    <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-700/50">
                    <tr class="text-left text-gray-300 uppercase text-xs">
                        <th class="px-4 py-3 font-medium">Judul Tugas</th>
                        <th class="px-4 py-3 font-medium">Proyek</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Deadline</th>
                        <th class="px-4 py-3 font-medium">Progress</th>
                        <th class="px-4 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($tasks as $task)
                        @php
                            $deadline = $task->deadline ? \Carbon\Carbon::parse($task->deadline) : null;
                            $isOverdue = $deadline && $deadline->isPast() && $task->status !== 'done';
                            $isDueSoon = $deadline && $deadline->isFuture() && $deadline->diffInDays(now()) <= 2 && $task->status !== 'done';
                            $daysLeft = $deadline ? now()->diffInDays($deadline, false) : null;
                        @endphp
                        <tr class="hover:bg-gray-700/30 transition {{ $isOverdue ? 'bg-red-900/10' : ($isDueSoon ? 'bg-orange-900/10' : '') }}">
                            <td class="px-4 py-3">
                                <p class="font-medium text-white">{{ $task->title }}</p>
                                @if($task->description)
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-1">{{ $task->description }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-gray-300">{{ $task->project->name ?? '-' }}</p>
                                @if($task->project && $task->project->category)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-blue-900/50 text-blue-300 border border-blue-500/30">
                                        {{ ucfirst($task->project->category) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full font-semibold
                                    @if($task->status === 'done') bg-green-900/50 text-green-300 border border-green-500/30
                                    @elseif($task->status === 'ongoing') bg-yellow-900/50 text-yellow-300 border border-yellow-500/30
                                    @else bg-gray-700 text-gray-300 border border-gray-600 @endif">
                                    @if($task->status === 'done') ✅ Selesai
                                    @elseif($task->status === 'ongoing') 🔄 Dikerjakan
                                    @else ⏳ Pending @endif
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($deadline)
                                    <p class="text-gray-300 {{ $isOverdue ? 'text-red-400 font-semibold' : ($isDueSoon ? 'text-orange-400' : '') }}">
                                        {{ $deadline->format('d/m/Y') }}
                                    </p>
                                    @if($isOverdue)
                                        <p class="text-xs text-red-400">+{{ abs($daysLeft) }} hari terlambat</p>
                                    @elseif($isDueSoon)
                                        <p class="text-xs text-orange-400">H-{{ $daysLeft }}</p>
                                    @endif
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-gray-700 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ $isOverdue ? 'bg-red-500' : 'bg-blue-500' }}" 
                                             style="width: {{ $task->progress ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $task->progress ?? 0 }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex gap-2 justify-end">
                                    <a href="{{ route('employee.tasks.show', $task) }}" 
                                       class="px-3 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded transition">
                                        Detail
                                    </a>
                                    @if($task->status !== 'done')
                                        <a href="{{ route('employee.tasks.submit.form', $task) }}" 
                                           class="px-3 py-1 text-xs bg-green-600 hover:bg-green-700 text-white rounded transition">
                                            Selesai
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada tugas yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-4">
        {{ $tasks->appends(request()->query())->links() }}
    </div>
</div>
@endsection
