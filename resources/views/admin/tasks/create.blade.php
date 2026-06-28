@extends('layouts.app')

@section('title', 'Buat Task List')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">➕ Buat Task List</h1>
        <p class="text-gray-400">Tambahkan task untuk divisi terpilih yang terlibat dalam proyek</p>
    </div>

    <form action="{{ route('admin.tasks.store') }}" method="POST" id="taskForm">
        @csrf
        <input type="hidden" name="project_id" value="{{ $project->id }}">

        {{-- INFO PROYEK --}}
        <div class="bg-blue-900/30 border border-blue-500/30 p-4 rounded-lg mb-6">
            <h3 class="text-lg font-semibold text-white mb-2">{{ $project->name }}</h3>
            <p class="text-sm text-gray-300">Kategori: {{ ucfirst($project->category) }}</p>
            <p class="text-sm text-gray-300">Deadline Proyek: {{ $project->deadline ? \Carbon\Carbon::parse($project->deadline)->format('d/m/Y') : '-' }}</p>
        </div>

        {{-- DAFTAR DIVISI & TASK --}}
        @if($project->divisions->count() > 0)
            {{-- PILIH DIVISI --}}
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-6">
                <div class="mb-4">
                    <label class="block text-sm text-gray-400 mb-2 font-medium">Pilih Divisi untuk Membuat Task *</label>
                    <select name="division_id" id="division_select" required class="w-full bg-gray-700 border border-gray-600 rounded-lg p-3 text-white focus:outline-none focus:border-blue-500">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($project->divisions as $division)
                            <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>
                                {{ strtoupper($division->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- DAFTAR TASK --}}
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        📋 Daftar Task
                    </h3>
                    <button type="button" onclick="addTask()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition text-sm">
                        + Tambah Task
                    </button>
                </div>

                <div id="tasks-container" class="space-y-4">
                    {{-- Task pertama (default) --}}
                    <div class="task-item bg-gray-700/50 p-4 rounded-lg border border-gray-600" data-index="0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Judul Task *</label>
                                <input type="text" name="tasks[0][title]" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white" placeholder="Contoh: Desain UI Homepage">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Deadline *</label>
                                <input type="date" name="tasks[0][deadline]" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm text-gray-400 mb-1">Deskripsi</label>
                            <textarea name="tasks[0][description]" rows="2" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white" placeholder="Jelaskan detail pekerjaan..."></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Tanggal Mulai</label>
                                <input type="date" name="tasks[0][planned_start_date]" value="{{ date('Y-m-d') }}" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Target SLA (%)</label>
                                <input type="number" name="tasks[0][sla_target]" value="100" min="0" max="100" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-yellow-900/20 border border-yellow-500/30 rounded-lg p-6 text-center">
                <p class="text-yellow-300 text-lg mb-2">⚠️ Belum ada divisi yang dipilih untuk proyek ini</p>
                <p class="text-gray-400 text-sm">Silakan edit proyek terlebih dahulu untuk memilih divisi yang terlibat</p>
                <a href="{{ route('admin.projects.edit', $project) }}" class="inline-block mt-4 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    Edit Proyek
                </a>
            </div>
        @endif

        {{-- INFO NOTIFIKASI --}}
        @if($project->divisions->count() > 0)
        <div class="bg-blue-900/20 border border-blue-500/30 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-blue-300">
                    <p class="font-semibold mb-1">📢 Notifikasi Otomatis</p>
                    <p class="text-xs text-gray-400">Setelah task disimpan, sistem akan otomatis mengirim notifikasi WhatsApp, Email, dan Google Calendar ke kepala divisi terpilih.</p>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition font-semibold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                💾 Simpan Task
            </button>
            <a href="{{ route('admin.tasks.index.by.project', $project->id) }}" class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition font-semibold">
                Batal
            </a>
        </div>
        @endif
    </form>
</div>

<script>
let taskCounter = 1;

function addTask() {
    const container = document.getElementById('tasks-container');
    const index = taskCounter;
    
    const taskHtml = `
        <div class="task-item bg-gray-700/50 p-4 rounded-lg border border-gray-600" data-index="${index}">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-white font-semibold">Task ${index + 1}</h4>
                <button type="button" onclick="removeTask(this)" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-xs">
                    Hapus
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Judul Task *</label>
                    <input type="text" name="tasks[${index}][title]" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Deadline *</label>
                    <input type="date" name="tasks[${index}][deadline]" required class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                </div>
            </div>
            <div class="mb-3">
                <label class="block text-sm text-gray-400 mb-1">Deskripsi</label>
                <textarea name="tasks[${index}][description]" rows="2" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Tanggal Mulai</label>
                    <input type="date" name="tasks[${index}][planned_start_date]" value="${new Date().toISOString().split('T')[0]}" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Target SLA (%)</label>
                    <input type="number" name="tasks[${index}][sla_target]" value="100" min="0" max="100" class="w-full bg-gray-700 border border-gray-600 rounded p-2 text-white">
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', taskHtml);
    taskCounter++;
}

function removeTask(button) {
    const taskItem = button.closest('.task-item');
    taskItem.remove();
}
</script>
@endsection