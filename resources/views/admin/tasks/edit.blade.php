@extends('layouts.app')

@section('title', 'Edit Task')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Edit Task</h1>
        <p class="text-gray-400">{{ $task->project?->name }}</p>
    </div>

    <form method="POST" action="{{ route('admin.tasks.update', $task) }}" class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-5">
        @csrf
        @method('PUT')

        <input type="hidden" name="project_id" value="{{ $task->project_id }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Judul Task *</label>
                <input name="title" value="{{ old('title', $task->title) }}" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                @error('title') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Status *</label>
                <select name="status" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                    @foreach(['pending' => 'Pending', 'ongoing' => 'Ongoing', 'done' => 'Selesai'] as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $task->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-400 mb-1">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">{{ old('description', $task->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Divisi *</label>
                <select name="division_id" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" {{ old('division_id', $task->division_id) == $division->id ? 'selected' : '' }}>
                            {{ $division->name }}
                        </option>
                    @endforeach
                </select>
                @error('division_id') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Pegawai *</label>
                <select name="assigned_to" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} - {{ $user->bidang ?? '-' }} / {{ $user->jabatan ?? '-' }}
                        </option>
                    @endforeach
                </select>
                @error('assigned_to') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Tanggal Mulai *</label>
                <input type="date" name="planned_start_date" value="{{ old('planned_start_date', $task->planned_start_date?->format('Y-m-d')) }}" min="{{ $task->project?->start_date?->format('Y-m-d') }}" max="{{ $task->project?->deadline?->format('Y-m-d') }}" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                @error('planned_start_date') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Deadline / Target *</label>
                <input type="date" name="deadline" value="{{ old('deadline', $task->deadline?->format('Y-m-d')) }}" min="{{ $task->project?->start_date?->format('Y-m-d') }}" max="{{ $task->project?->deadline?->format('Y-m-d') }}" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                @error('deadline') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="rounded-lg border border-gray-600 bg-gray-900/40 p-3 text-sm text-gray-300">
            Nilai SLA maksimal: 100%. SLA aktual dihitung otomatis dari tanggal mulai, deadline, dan tanggal selesai aktual.
        </div>

        <div>
            <label class="block text-sm text-gray-400 mb-1">Alasan Keterlambatan</label>
            <textarea name="delay_reason" rows="2" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">{{ old('delay_reason', $task->delay_reason) }}</textarea>
        </div>

        <div class="flex gap-3 pt-4 border-t border-gray-700">
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">Update Task</button>
            <a href="{{ route('admin.tasks.index.by.project', $task->project_id) }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">Batal</a>
        </div>
    </form>
</div>
@endsection
