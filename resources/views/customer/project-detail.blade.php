@extends('layouts.customer')

@section('title', 'Detail Proyek Customer')

@section('content')
<div class="p-6">
    <a href="{{ route('customer.projects') }}" class="text-blue-400 hover:underline text-sm">Kembali ke Daftar Proyek</a>

    <div class="mt-4 bg-gray-800 border border-gray-700 rounded-lg p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $project->name }}</h1>
                <p class="text-gray-400">{{ ucfirst($project->category) }} · Deadline: {{ $project->deadline?->format('d/m/Y') ?? '-' }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm {{ $project->status === 'done' ? 'bg-green-900/50 text-green-300' : 'bg-blue-900/50 text-blue-300' }}">
                {{ ucfirst($project->status) }}
            </span>
        </div>

        <div class="mt-6">
            <div class="flex justify-between text-sm text-gray-300 mb-2">
                <span>Progress Proyek</span>
                <span>{{ \App\Models\ProjectTask::formatSlaPercentage($project->overall_progress) }}</span>
            </div>
            <div class="h-3 bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-green-500" style="width: {{ $project->overall_progress }}%"></div>
            </div>
        </div>
    </div>

    <div class="mt-6 bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <h2 class="text-xl font-semibold text-white">Task List</h2>
            <p class="text-sm text-gray-400">Customer hanya dapat melihat status pengerjaan task.</p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-700 text-gray-300">
                <tr>
                    <th class="text-left px-4 py-3">Task</th>
                    <th class="text-left px-4 py-3">Divisi</th>
                    <th class="text-left px-4 py-3">Deadline</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($project->tasks as $task)
                    <tr>
                        <td class="px-4 py-4 text-white font-medium">{{ $task->title }}</td>
                        <td class="px-4 py-4 text-gray-300">{{ $task->division?->name ?? '-' }}</td>
                        <td class="px-4 py-4 text-gray-300">{{ $task->deadline?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-4 text-gray-300">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td>
                        <td class="px-4 py-4 text-gray-300">{{ $task->progress ?? 0 }}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">Task belum tersedia.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
