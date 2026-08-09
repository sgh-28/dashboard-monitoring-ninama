@extends('layouts.app')

@section('title', 'Daftar Proyek Pegawai')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Daftar Proyek</h1>
        <p class="text-gray-400">
            {{ $isProjectManagement ? 'Proyek yang dapat dimonitor oleh Project Management sesuai bidang.' : 'Proyek yang memiliki task untuk Anda.' }}
        </p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-700 text-gray-300">
                <tr>
                    <th class="text-left px-4 py-3">Nama Proyek</th>
                    <th class="text-left px-4 py-3">Customer</th>
                    <th class="text-left px-4 py-3">Bidang</th>
                    <th class="text-left px-4 py-3">Progress</th>
                    <th class="text-left px-4 py-3">Task</th>
                    <th class="text-right px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($projects as $project)
                    <tr class="hover:bg-gray-700/40">
                        <td class="px-4 py-4 font-semibold text-white">{{ $project->name }}</td>
                        <td class="px-4 py-4 text-gray-300">{{ $project->customer?->company ?? $project->customer?->name ?? '-' }}</td>
                        <td class="px-4 py-4">
                            <span class="px-2 py-1 rounded-full bg-blue-900/50 text-blue-300 border border-blue-600/40">
                                {{ ucfirst($project->category) }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-gray-300">
                            <div class="flex items-center gap-3">
                                <div class="w-32 h-2 bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-green-500" style="width: {{ $project->overall_progress }}%"></div>
                                </div>
                                <span>{{ \App\Models\ProjectTask::formatSlaPercentage($project->overall_progress) }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-gray-300">{{ $project->tasks->count() }} task</td>
                        <td class="px-4 py-4 text-right">
                            @if($isProjectManagement)
                                <a href="{{ route('employee.tasks.projects.show', $project) }}" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 rounded text-white">Detail</a>
                            @else
                                <a href="{{ route('employee.tasks.index') }}" class="px-3 py-2 bg-gray-600 hover:bg-gray-700 rounded text-white">Lihat Task</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada proyek yang dapat ditampilkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
