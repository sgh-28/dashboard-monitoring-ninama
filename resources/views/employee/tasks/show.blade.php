@extends('layouts.app')

@section('title', 'Detail Tugas')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('employee.tasks.index') }}" class="text-blue-400 hover:underline text-sm">← Kembali ke Daftar Tugas</a>
    </div>

    @php
        $deadline = $task->deadline ? \Carbon\Carbon::parse($task->deadline) : null;
        $isOverdue = $deadline && $deadline->isPast() && $task->status !== 'done';
        $isDueSoon = $deadline && $deadline->isFuture() && $deadline->diffInDays(now()) <= 2 && $task->status !== 'done';
    @endphp

    {{-- HEADER CARD --}}
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-6 {{ $isOverdue ? 'border-l-4 border-l-red-500' : ($isDueSoon ? 'border-l-4 border-l-orange-500' : '') }}">
        <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-white mb-2">{{ $task->title }}</h1>
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="px-3 py-1 text-xs rounded-full font-semibold
                        @if($task->status === 'done') bg-green-900/50 text-green-300 border border-green-500/30
                        @elseif($task->status === 'ongoing') bg-yellow-900/50 text-yellow-300 border border-yellow-500/30
                        @else bg-gray-700 text-gray-300 border border-gray-600 @endif">
                        @if($task->status === 'done') ✅ Selesai
                        @elseif($task->status === 'ongoing') 🔄 Dalam Pengerjaan
                        @else ⏳ Pending @endif
                    </span>
                    @if($task->project && $task->project->category)
                        <span class="px-3 py-1 text-xs rounded-full bg-blue-900/50 text-blue-300 border border-blue-500/30">
                            {{ ucfirst($task->project->category) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- INFO GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 pt-6 border-t border-gray-700">
            <div>
                <p class="text-xs text-gray-400 mb-1">📦 Proyek</p>
                <p class="text-white font-medium">{{ $task->project->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">📅 Deadline</p>
                <p class="text-white font-medium {{ $isOverdue ? 'text-red-400' : ($isDueSoon ? 'text-orange-400' : '') }}">
                    @if($deadline)
                        {{ $deadline->format('d F Y') }}
                        @if($isOverdue) <span class="text-xs block">(+{{ \App\Models\ProjectTask::formatDurationFromDays(abs(now()->diffInDays($deadline, false))) }} terlambat)</span>
                        @elseif($isDueSoon) <span class="text-xs block">(H-{{ now()->diffInDays($deadline, false) }})</span>
                        @endif
                    @else
                        <span class="text-gray-500">Tidak ada deadline</span>
                    @endif
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">👤 Ditugaskan Kepada</p>
                <p class="text-white font-medium">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500">{{ Auth::user()->jabatan ?? 'Pegawai' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 mb-1">📊 Progress</p>
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-gray-700 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $isOverdue ? 'bg-red-500' : 'bg-blue-500' }}" 
                             style="width: {{ $task->progress ?? 0 }}%"></div>
                    </div>
                    <span class="text-white font-medium">{{ $task->progress ?? 0 }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- DESKRIPSI --}}
    @if($task->description)
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-6">
        <h3 class="text-lg font-semibold text-white mb-3">📝 Deskripsi Tugas</h3>
        <p class="text-gray-300 whitespace-pre-line">{{ $task->description }}</p>
    </div>
    @endif

    @if($task->verification_status === 'revision_requested' && $task->verification_notes)
    <div class="bg-red-900/20 rounded-lg p-6 border border-red-500/40 mb-6">
        <h3 class="text-lg font-semibold text-red-200 mb-3">Catatan Revisi Project Management</h3>
        <p class="text-red-100 whitespace-pre-line">{{ $task->verification_notes }}</p>
        <p class="text-xs text-red-200/70 mt-3">Silakan perbaiki pekerjaan lalu kirim ulang laporan terbaru.</p>
    </div>
    @endif

    @if($task->submissions->isNotEmpty())
    <details class="bg-gray-800 rounded-lg p-6 border border-gray-700 mb-6">
        <summary class="cursor-pointer text-lg font-semibold text-white">Riwayat Pengerjaan</summary>
        <div class="mt-4 space-y-4">
            @foreach($task->submissions as $submission)
                <div class="rounded-lg border border-gray-700 bg-gray-900/40 p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-medium text-white">{{ $submission->status_label }}</p>
                            <p class="text-xs text-gray-500">{{ $submission->created_at->format('d F Y, H:i') }}</p>
                        </div>
                        @if($submission->reviewer)
                            <p class="text-xs text-gray-400">Dicek oleh {{ $submission->reviewer->name }}</p>
                        @endif
                    </div>
                    @if($submission->completion_notes)
                        <p class="mt-3 text-sm text-gray-300 whitespace-pre-line">{{ $submission->completion_notes }}</p>
                    @endif
                    @if($submission->proof_image)
                        <a href="{{ asset('storage/' . $submission->proof_image) }}" target="_blank" class="mt-3 inline-flex text-xs text-blue-400 hover:underline">Lihat bukti foto</a>
                    @endif
                    @if($submission->revision_notes)
                        <div class="mt-3 rounded border border-red-500/30 bg-red-900/20 p-3">
                            <p class="text-xs text-red-200 mb-1">Catatan revisi:</p>
                            <p class="text-sm text-red-100 whitespace-pre-line">{{ $submission->revision_notes }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </details>
    @endif

    {{-- BUKTI PENGERJAAN (Jika sudah selesai) --}}
    @if($task->status === 'done')
    <div class="bg-gray-800 rounded-lg p-6 border border-green-500/30 mb-6">
        <h3 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Bukti Pengerjaan
        </h3>
        
        @if($task->completion_notes)
        <div class="mb-4">
            <p class="text-xs text-gray-400 mb-1">Keterangan:</p>
            <p class="text-gray-300 whitespace-pre-line">{{ $task->completion_notes }}</p>
        </div>
        @endif

        @if($task->proof_image)
        <div>
            <p class="text-xs text-gray-400 mb-2">Bukti Foto:</p>
            <img src="{{ asset('storage/' . $task->proof_image) }}" 
                 alt="Bukti Pengerjaan" 
                 class="max-w-md rounded-lg border border-gray-700">
        </div>
        @endif

        @if($task->completed_at)
        <p class="text-xs text-gray-500 mt-4">
            Diselesaikan pada: {{ \Carbon\Carbon::parse($task->completed_at)->format('d F Y, H:i') }}
        </p>
        @endif
    </div>
    @endif

    {{-- ACTION BUTTONS --}}
    @if($task->status !== 'done')
    <div class="flex gap-3">
        @if($task->status === 'pending')
            <form action="{{ route('employee.tasks.submit', $task) }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="ongoing">
                <button type="submit" class="px-6 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition font-medium">
                    ▶️ Mulai Kerjakan
                </button>
            </form>
        @elseif($task->status === 'ongoing')
            <span class="px-6 py-2 bg-blue-600/20 text-blue-300 border border-blue-500/30 rounded-lg font-medium">
                🔄 On Going
            </span>
        @endif
        
        <a href="{{ route('employee.tasks.submit.form', $task) }}" 
           class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition font-medium">
            📤 Tandai Selesai
        </a>
        
        <a href="{{ route('employee.tasks.index') }}" 
           class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition font-medium">
            Kembali
        </a>
    </div>
    @endif
</div>
@endsection
