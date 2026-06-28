{{-- resources/views/admin/projects/manage.blade.php --}}
@extends('layouts.adminlte')

@section('title', 'Manage Project - ' . $project->name)

@section('content')
<div class="p-6">
    {{-- HEADER --}}
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                🔧 Manage Project
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $project->name }}</p>
        </div>
        <a href="{{ route('admin.projects.index') }}" 
           class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition">
            ← Kembali
        </a>
    </div>

    {{-- PROJECT INFO CARD --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Customer</p>
                <p class="font-semibold text-gray-800 dark:text-gray-200">
                    {{ $project->customer->company ?? $project->customer->name ?? '-' }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Kategori</p>
                <p class="font-semibold text-gray-800 dark:text-gray-200">{{ ucfirst($project->category) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <span class="px-3 py-1 text-xs rounded-full font-semibold
                    @if($project->status === 'done') bg-green-100 text-green-700
                    @elseif($project->status === 'ongoing') bg-blue-100 text-blue-700
                    @elseif($project->status === 'rejected') bg-red-100 text-red-700
                    @else bg-yellow-100 text-yellow-700 @endif">
                    {{ ucfirst($project->status) }}
                </span>
            </div>
        </div>
    </div>

    {{-- DIVISIONS & TASKS --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">Divisi & Tugas</h2>
        </div>

        <div class="p-6">
            @if($project->divisions && $project->divisions->count() > 0)
                <div class="space-y-6">
                    @foreach($project->divisions as $division)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-5">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                                {{ $division->name }}
                            </h3>
                            <span class="text-sm text-gray-600 dark:text-gray-400">
                                {{ $division->tasks->where('status', 'done')->count() }} / {{ $division->tasks->count() }} Tugas Selesai
                            </span>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="mb-4">
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                @php
                                    $divProgress = $division->tasks->count() > 0 
                                        ? ($division->tasks->where('status', 'done')->count() / $division->tasks->count()) * 100 
                                        : 0;
                                @endphp
                                <div class="bg-blue-600 h-2 rounded-full transition-all" 
                                     style="width: {{ $divProgress }}%"></div>
                            </div>
                        </div>

                        {{-- Tasks List --}}
                        @if($division->tasks->count() > 0)
                        <div class="space-y-2">
                            @foreach($division->tasks as $task)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" 
                                           class="w-4 h-4 text-blue-600 rounded"
                                           {{ $task->status === 'done' ? 'checked' : '' }}
                                           disabled>
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ $task->name }}</p>
                                        @if($task->assignedTo)
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Assigned to: {{ $task->assignedTo->name }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full font-semibold
                                    @if($task->status === 'done') bg-green-100 text-green-700
                                    @elseif($task->status === 'in_progress') bg-yellow-100 text-yellow-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-center text-gray-500 py-4">Belum ada tugas untuk divisi ini</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">Belum ada divisi yang ditambahkan</p>
                    <a href="{{ route('admin.projects.edit', $project) }}" 
                       class="mt-4 inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                        Edit Project untuk Menambahkan Divisi
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- ASSIGN EMPLOYEE SECTION --}}
    @if($employees && $employees->count() > 0)
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-4">Assign Pegawai ke Proyek</h3>
        <form action="{{ route('admin.projects.assign', $project) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Pilih Pegawai
                    </label>
                    <select name="employee_id" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                        Assign Pegawai
                    </button>
                </div>
            </div>
        </form>
    </div>
    @endif
</div>
@endsection