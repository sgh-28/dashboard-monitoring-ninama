@extends('layouts.app')

@section('title', 'Detail Proyek: ' . $project->name)

@section('content')
@push('styles')
<style>
    html:not(.dark) .project-detail-page .project-panel {
        background: #eef2f7 !important;
        border-color: #cbd5e1 !important;
    }

    html:not(.dark) .project-detail-page .project-panel-title,
    html:not(.dark) .project-detail-page .project-table .primary-text {
        color: #0f172a !important;
    }

    html:not(.dark) .project-detail-page .project-panel-subtitle,
    html:not(.dark) .project-detail-page .project-table .secondary-text {
        color: #334155 !important;
    }

    html:not(.dark) .project-detail-page .project-table {
        color: #0f172a;
    }

    html:not(.dark) .project-detail-page .project-table thead {
        background: #d8dee8 !important;
        color: #1e293b !important;
    }

    html:not(.dark) .project-detail-page .project-table th {
        color: #1e293b !important;
        font-weight: 700;
    }

    html:not(.dark) .project-detail-page .project-table tbody {
        background: #f3f6fa;
    }

    html:not(.dark) .project-detail-page .project-table tbody tr {
        border-color: #cbd5e1 !important;
    }

    html:not(.dark) .project-detail-page .project-table tbody tr:nth-child(even) {
        background: #e8edf4;
    }

    html:not(.dark) .project-detail-page .project-table tbody tr:hover {
        background: #dfe7f1 !important;
    }

    html:not(.dark) .project-detail-page .project-table tbody tr.is-late {
        background: #eadde0 !important;
    }
</style>
@endpush

<div class="project-detail-page p-6">
    {{-- HEADER --}}
    <div class="mb-6 flex justify-between items-start">
        <div>
            <a href="{{ (Auth::user()?->role?->name ?? '') === 'pegawai' ? route('employee.tasks.index') : route('projects.category.detail', ['category' => $project->category]) }}" 
               class="text-blue-400 hover:underline text-sm mb-2 inline-block">
               ← Kembali ke Daftar Proyek
            </a>
            <h1 class="text-2xl font-bold text-white">{{ $project->name }}</h1>
            <p class="text-gray-400">
                {{ $project->customer?->company ?? '-' }} 
                | {{ ucfirst($project->category) }}
                @if($project->deadline)
                | Deadline: {{ \Carbon\Carbon::parse($project->deadline)->format('d M Y') }}
                @endif
            </p>
        </div>
        <span class="px-3 py-1 rounded-full text-sm font-semibold
            @if($project->status === 'done') bg-green-500/20 text-green-400
            @elseif($project->status === 'ongoing') bg-blue-500/20 text-blue-400
            @else bg-gray-500/20 text-gray-400 @endif">
            {{ ucfirst($project->status) }}
        </span>
    </div>

    @isset($timelineData)
        <div class="mb-6">
            @include('components.project-milestone-timeline', ['timelineData' => $timelineData])
        </div>
    @endisset

    @php
        $userRole = Auth::user()?->role?->name ?? '';
        $ratingLabels = [
            1 => 'Sangat Tidak Puas',
            2 => 'Tidak Puas',
            3 => 'Cukup Puas',
            4 => 'Puas',
            5 => 'Sangat Puas',
        ];
    @endphp

    @if($userRole === 'direktur' && $project->status === 'done' && $project->customer_feedback_submitted_at)
        <div class="project-panel mb-6 rounded-lg border border-gray-700 bg-gray-800 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div>
                    <h3 class="project-panel-title text-lg font-semibold text-white">Feedback Customer</h3>
                    <p class="project-panel-subtitle mt-1 text-sm text-gray-400">
                        Review customer setelah proyek dinyatakan selesai.
                    </p>
                </div>
                <div class="rounded-lg border border-yellow-500/30 bg-yellow-500/10 px-4 py-3 text-right">
                    <p class="text-lg font-semibold text-yellow-300">{{ str_repeat('⭐', (int) $project->customer_satisfaction_rating) }}</p>
                    <p class="mt-1 text-sm text-gray-300">{{ $ratingLabels[(int) $project->customer_satisfaction_rating] ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-5 rounded-lg border border-gray-700 bg-gray-900/40 p-4">
                <p class="text-sm text-gray-400">
                    Bagaimana pendapat Anda mengenai hasil pengerjaan proyek yang telah diselesaikan?
                </p>
                <p class="mt-3 whitespace-pre-line text-gray-200">{{ $project->customer_feedback }}</p>
                <p class="mt-4 text-xs text-gray-500">
                    Dikirim pada {{ $project->customer_feedback_submitted_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>
    @endif

    {{-- TASKLIST READ ONLY UNTUK ADMIN / DIREKTUR --}}
    <div class="project-panel bg-gray-800 rounded-lg p-6 border border-gray-700 mb-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="project-panel-title text-lg font-semibold text-white">Tasklist Proyek</h3>
                <p class="project-panel-subtitle text-sm text-gray-400">Read-only untuk monitoring status, keterlambatan, dan laporan pengerjaan pegawai.</p>
            </div>
            <span class="project-panel-subtitle text-xs text-gray-400">
                {{ $project->tasks->where('status', 'done')->count() }}/{{ $project->tasks->count() }} task selesai
                <span class="mx-2">|</span>
                {{ $project->tasks->where('verification_status', 'approved')->count() }}/{{ $project->tasks->count() }} task disetujui PM
            </span>
        </div>

        @if($project->tasks->count() > 0)
            <div class="overflow-x-auto">
                <table class="project-table w-full text-sm">
                    <thead class="bg-gray-700/60 text-gray-300 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Task</th>
                            <th class="px-4 py-3 text-left">Divisi</th>
                            <th class="px-4 py-3 text-left">Pegawai</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">Verifikasi PM</th>
                            <th class="px-4 py-3 text-left">SLA Task</th>
                            <th class="px-4 py-3 text-left">Deadline</th>
                            <th class="px-4 py-3 text-left">Laporan Pengerjaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($project->tasks->sortBy('deadline') as $task)
                            @php
                                $deadline = $task->deadline ? \Carbon\Carbon::parse($task->deadline)->startOfDay() : null;
                                $finishDate = $task->actual_end_date ?? $task->completed_at;
                                $finishDate = $finishDate ? \Carbon\Carbon::parse($finishDate)->startOfDay() : null;
                                $isLate = $deadline && (
                                    ($task->status !== 'done' && now()->startOfDay()->gt($deadline)) ||
                                    ($task->status === 'done' && $finishDate && $finishDate->gt($deadline))
                                );
                                $lateDays = $isLate
                                    ? (int) $deadline->diffInDays($finishDate ?? now()->startOfDay())
                                    : 0;
                            @endphp
                            <tr class="hover:bg-gray-700/30 {{ $isLate ? 'is-late bg-red-900/10' : '' }}">
                                <td class="px-4 py-3">
                                    <p class="primary-text font-medium text-white">{{ $task->title }}</p>
                                    @if($task->description)
                                        <p class="secondary-text text-xs text-gray-500 mt-1">{{ Str::limit($task->description, 70) }}</p>
                                    @endif
                                </td>
                                <td class="secondary-text px-4 py-3 text-gray-300">{{ $task->division?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <p class="secondary-text text-gray-300">{{ $task->assignee?->name ?? '-' }}</p>
                                    <p class="secondary-text text-xs text-gray-500">{{ $task->assignee?->jabatan ?? '' }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $task->status_color }}">
                                        {{ $task->status_label }}
                                    </span>
                                    @if($isLate)
                                        <p class="text-xs text-red-400 mt-2">Terlambat {{ \App\Models\ProjectTask::formatDurationFromDays($lateDays) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $task->verification_status_color }}">
                                        {{ $task->verification_status_label }}
                                    </span>
                                    @if($task->verified_at)
                                        <p class="text-xs text-gray-500 mt-2">{{ $task->verified_at->format('d/m/Y H:i') }}</p>
                                    @endif
                                    @if($task->verifier)
                                        <p class="text-xs text-gray-500">{{ $task->verifier->name }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-emerald-400">{{ $task->task_sla_percentage_formatted }}</p>
                                    <p class="text-xs text-gray-500">{{ str_replace('_', ' ', $task->sla_evaluation_status) }}</p>
                                    @if($task->sla_evaluation_reason)
                                        <p class="text-xs text-amber-400 mt-1">{{ $task->sla_evaluation_reason }}</p>
                                    @endif
                                </td>
                                <td class="secondary-text px-4 py-3 text-gray-300">
                                    {{ $deadline ? $deadline->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($task->status === 'done')
                                        @if($task->completion_notes)
                                            <p class="secondary-text text-gray-300">{{ Str::limit($task->completion_notes, 90) }}</p>
                                        @else
                                            <p class="secondary-text text-gray-500">Tidak ada keterangan.</p>
                                        @endif
                                        @if($task->proof_image)
                                            <a href="{{ asset('storage/' . $task->proof_image) }}" target="_blank" class="inline-flex mt-2 text-xs text-blue-400 hover:underline">
                                                Lihat bukti foto
                                            </a>
                                        @else
                                            <p class="text-xs text-gray-500 mt-2">Bukti foto belum tersedia.</p>
                                        @endif
                                        @if($task->submissions->isNotEmpty())
                                            <details class="mt-3 rounded border border-gray-700 bg-gray-900/40 p-2">
                                                <summary class="cursor-pointer text-xs font-medium text-blue-300">Riwayat pengerjaan</summary>
                                                <div class="mt-2 space-y-2">
                                                    @foreach($task->submissions as $submission)
                                                        <div class="rounded border border-gray-700 p-2 text-xs">
                                                            <div class="flex justify-between gap-2 text-gray-400">
                                                                <span>{{ $submission->status_label }}</span>
                                                                <span>{{ $submission->created_at->format('d/m/Y H:i') }}</span>
                                                            </div>
                                                            @if($submission->completion_notes)
                                                                <p class="mt-2 text-gray-300 whitespace-pre-line">{{ Str::limit($submission->completion_notes, 120) }}</p>
                                                            @endif
                                                            @if($submission->proof_image)
                                                                <a href="{{ asset('storage/' . $submission->proof_image) }}" target="_blank" class="mt-2 inline-flex text-blue-400 hover:underline">Lihat bukti</a>
                                                            @endif
                                                            @if($submission->revision_notes)
                                                                <p class="mt-2 text-red-300 whitespace-pre-line">Revisi: {{ $submission->revision_notes }}</p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                        @if(isset($canVerifyTasks) && $canVerifyTasks && $task->verification_status === 'pending_review')
                                            <div class="mt-3 space-y-3">
                                                <form action="{{ route('employee.tasks.tasks.approve', $task) }}" method="POST" class="space-y-2">
                                                    @csrf
                                                    <textarea name="verification_notes" rows="2"
                                                              class="w-full rounded border border-gray-600 bg-gray-700 px-3 py-2 text-xs text-white"
                                                              placeholder="Catatan approval Project Management (opsional)"></textarea>
                                                    <button type="submit"
                                                            class="rounded bg-green-600 px-3 py-1 text-xs font-medium text-white transition hover:bg-green-700">
                                                        Approve
                                                    </button>
                                                </form>
                                                <form action="{{ route('employee.tasks.tasks.revision', $task) }}" method="POST" class="space-y-2">
                                                    @csrf
                                                    <textarea name="revision_notes" rows="2" required
                                                              class="w-full rounded border border-red-500/40 bg-red-950/30 px-3 py-2 text-xs text-white"
                                                              placeholder="Tulis bagian yang harus direvisi oleh pegawai"></textarea>
                                                    <button type="submit"
                                                            class="rounded bg-red-600 px-3 py-1 text-xs font-medium text-white transition hover:bg-red-700">
                                                        Revisi
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                        @if($task->verification_notes)
                                            <div class="mt-3 rounded border border-gray-700 bg-gray-900/40 p-2">
                                                <p class="text-xs text-gray-500 mb-1">Catatan PM:</p>
                                                <p class="text-xs text-gray-300 whitespace-pre-line">{{ $task->verification_notes }}</p>
                                            </div>
                                        @endif
                                    @else
                                        @if($task->verification_status === 'revision_requested' && $task->verification_notes)
                                            <div class="rounded border border-red-500/30 bg-red-900/20 p-2">
                                                <p class="text-xs text-red-200 mb-1">Catatan revisi terakhir:</p>
                                                <p class="text-xs text-red-100 whitespace-pre-line">{{ $task->verification_notes }}</p>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500">Belum ada laporan.</span>
                                        @endif

                                        @if($task->submissions->isNotEmpty())
                                            <details class="mt-3 rounded border border-gray-700 bg-gray-900/40 p-2">
                                                <summary class="cursor-pointer text-xs font-medium text-blue-300">Riwayat pengerjaan</summary>
                                                <div class="mt-2 space-y-2">
                                                    @foreach($task->submissions as $submission)
                                                        <div class="rounded border border-gray-700 p-2 text-xs">
                                                            <div class="flex justify-between gap-2 text-gray-400">
                                                                <span>{{ $submission->status_label }}</span>
                                                                <span>{{ $submission->created_at->format('d/m/Y H:i') }}</span>
                                                            </div>
                                                            @if($submission->completion_notes)
                                                                <p class="mt-2 text-gray-300 whitespace-pre-line">{{ Str::limit($submission->completion_notes, 120) }}</p>
                                                            @endif
                                                            @if($submission->proof_image)
                                                                <a href="{{ asset('storage/' . $submission->proof_image) }}" target="_blank" class="mt-2 inline-flex text-blue-400 hover:underline">Lihat bukti</a>
                                                            @endif
                                                            @if($submission->revision_notes)
                                                                <p class="mt-2 text-red-300 whitespace-pre-line">Revisi: {{ $submission->revision_notes }}</p>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </details>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-400 text-center py-6">Belum ada task untuk proyek ini.</p>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- KOLOM KIRI: PROGRESS DETAILS --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- PROGRESS BAR PER DIVISI --}}
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">📊 Progress Details</h3>
                
                @if($project->divisions->count() > 0)
                <div class="space-y-4">
                    @foreach($project->divisions as $division)
                    @php
                        $totalTasks = $division->tasks->count();
                        $completedTasks = $division->tasks->where('status', 'done')->count();
                        $divisionProgress = $totalTasks > 0 ? round((float) $division->tasks->avg(fn($task) => (int) ($task->progress ?? 0)), 2) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-gray-300">{{ $division->name }}</span>
                            <span class="text-gray-400">{{ \App\Models\ProjectTask::formatSlaPercentage($divisionProgress) }}</span>
                        </div>
                        <div class="w-full bg-gray-700 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full transition-all duration-500
                                @if($divisionProgress >= 100) bg-green-500
                                @elseif($divisionProgress > 0) bg-blue-500
                                @else bg-purple-500 @endif"
                                style="width: {{ $divisionProgress }}%"></div>
                        </div>
                        <p class="text-xs mt-1 text-gray-500">
                            {{ $completedTasks }}/{{ $totalTasks }} task selesai
                        </p>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-400 text-center py-4">
                    Data divisi proyek belum tersedia.
                </p>
                @endif
                
                {{-- Overall Progress --}}
                <div class="mt-6 pt-4 border-t border-gray-700">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-white font-semibold">Overall Progress</span>
                        <span class="text-blue-400 font-bold">{{ $overallProgress }}%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 rounded-full" 
                             style="width: {{ $overallProgress }}%"></div>
                    </div>
                </div>
            </div>

            {{-- PROJECT TIMELINE VISUAL --}}
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-6">🚀 Project Timeline</h3>
                
                @php
                    $projectTasks = $project->tasks ?? collect();
                    $totalProjectTasks = $projectTasks->count();
                    $doneProjectTasks = $projectTasks->where('status', 'done')->count();
                    $approvedProjectTasks = $projectTasks->where('verification_status', 'approved')->count();
                    $testingTasks = $projectTasks->filter(function ($task) {
                        $divisionName = strtolower((string) optional($task->division)->name);

                        return str_contains($divisionName, 'testing') || str_contains($divisionName, 'test');
                    });
                    $testingDoneTasks = $testingTasks->where('status', 'done')->count();
                    $hasCustomerAccount = filled($project->customer_id);
                    $hasStartedWork = $projectTasks->contains(fn($task) => in_array($task->status, ['ongoing', 'in_progress', 'done', 'completed'], true));
                    $allTasksDone = $totalProjectTasks > 0 && $doneProjectTasks === $totalProjectTasks;
                    $allTasksApproved = $totalProjectTasks > 0 && $approvedProjectTasks === $totalProjectTasks;
                    $testingReached = $testingTasks->isEmpty()
                        ? $hasStartedWork
                        : $testingTasks->contains(fn($task) => in_array($task->status, ['ongoing', 'in_progress', 'done', 'completed'], true));
                    $testingComplete = $testingTasks->isEmpty()
                        ? $allTasksDone
                        : $testingDoneTasks === $testingTasks->count();
                    $workflowSteps = [
                        [
                            'label' => 'Akun Customer',
                            'status' => $hasCustomerAccount ? 'completed' : 'pending',
                            'caption' => $hasCustomerAccount ? 'Selesai' : 'Menunggu',
                        ],
                        [
                            'label' => 'Pengerjaan Task',
                            'status' => $allTasksDone ? 'completed' : ($hasStartedWork ? 'ongoing' : 'pending'),
                            'caption' => $allTasksDone ? 'Selesai' : ($hasStartedWork ? 'Berjalan' : 'Menunggu'),
                        ],
                        [
                            'label' => 'Verifikasi PM',
                            'status' => $allTasksApproved ? 'completed' : ($approvedProjectTasks > 0 ? 'ongoing' : 'pending'),
                            'caption' => $allTasksApproved ? 'Selesai' : "{$approvedProjectTasks}/{$totalProjectTasks} disetujui",
                        ],
                        [
                            'label' => 'Testing',
                            'status' => $testingComplete ? 'completed' : ($testingReached ? 'ongoing' : 'pending'),
                            'caption' => $testingComplete ? 'Selesai' : ($testingReached ? 'Berjalan' : 'Menunggu'),
                        ],
                        [
                            'label' => 'Proyek Selesai',
                            'status' => $project->status === 'done' ? 'completed' : 'pending',
                            'caption' => $project->status === 'done' ? 'Selesai' : 'Menunggu',
                        ],
                    ];
                    $reachedSteps = collect($workflowSteps)->filter(fn($step) => in_array($step['status'], ['completed', 'ongoing'], true))->count();
                    $timelineFill = count($workflowSteps) > 1
                        ? max(0, min(100, (($reachedSteps - 1) / (count($workflowSteps) - 1)) * 100))
                        : 0;
                @endphp

                @if(count($workflowSteps) > 0)
                <div class="relative">
                    {{-- Garis Penghubung --}}
                    <div class="absolute top-5 left-0 w-full h-1 bg-gray-700 rounded"></div>
                    <div class="absolute top-5 left-0 h-1 bg-gradient-to-r from-green-500 via-blue-500 to-gray-600 rounded transition-all" 
                         style="width: {{ $timelineFill }}%"></div>
                    
                    {{-- Langkah-langkah --}}
                    <div class="relative grid grid-cols-5 gap-2">
                        @foreach($workflowSteps as $index => $step)
                        <div class="text-center">
                            {{-- Icon --}}
                            <div class="w-10 h-10 mx-auto rounded-full flex items-center justify-center mb-2 border-2 z-10 relative
                                @if($step['status'] === 'completed') bg-green-500 border-green-500 text-white
                                @elseif($step['status'] === 'ongoing') bg-blue-500 border-blue-500 text-white animate-pulse
                                @else bg-gray-700 border-gray-600 text-gray-400 @endif">
                                <span class="text-xs font-semibold">{{ $index + 1 }}</span>
                            </div>
                            <p class="text-xs text-gray-400 font-medium">{{ $step['label'] }}</p>
                            <p class="text-[10px] mt-1
                                @if($step['status'] === 'completed') text-green-400
                                @elseif($step['status'] === 'ongoing') text-blue-400
                                @else text-gray-500 @endif">
                                {{ $step['caption'] }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <p class="text-gray-400 text-center py-4">
                    Timeline belum tersedia.
                </p>
                @endif
            </div>
        </div>

        {{-- KOLOM KANAN: SLA & INFO --}}
        <div class="space-y-6">
            
            {{-- SLA PROYEK --}}
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">SLA Proyek</h3>

                <div class="space-y-3">
                    <div>
                        <p class="text-3xl font-bold text-emerald-400">{{ $project->sla_percentage_formatted }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $project->sla_status_text }} · {{ $project->evaluated_tasks_count }} dari {{ $project->total_tasks_count }} task sudah dinilai
                        </p>
                    </div>

                    <div class="w-full bg-gray-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-emerald-500 to-green-400 h-3 rounded-full"
                             style="width: {{ $project->sla_percentage ?? 0 }}%"></div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm pt-2">
                        <div class="rounded border border-gray-700 bg-gray-900/40 p-3">
                            <p class="text-xs text-gray-400">Tepat Waktu</p>
                            <p class="font-bold text-green-400">{{ $project->on_time_tasks_count }}</p>
                        </div>
                        <div class="rounded border border-gray-700 bg-gray-900/40 p-3">
                            <p class="text-xs text-gray-400">Terlambat</p>
                            <p class="font-bold text-red-400">{{ $project->late_tasks_count }}</p>
                        </div>
                        <div class="rounded border border-gray-700 bg-gray-900/40 p-3">
                            <p class="text-xs text-gray-400">Breached</p>
                            <p class="font-bold text-orange-400">{{ $project->breached_tasks_count }}</p>
                        </div>
                        <div class="rounded border border-gray-700 bg-gray-900/40 p-3">
                            <p class="text-xs text-gray-400">Total Task</p>
                            <p class="font-bold text-white">{{ $project->total_tasks_count }}</p>
                        </div>
                    </div>

                    @if($project->division_sla_summaries->isNotEmpty())
                        <div class="pt-3 border-t border-gray-700">
                            <p class="text-xs text-gray-400 mb-2">SLA per Divisi</p>
                            <div class="space-y-1 text-xs">
                                @foreach($project->division_sla_summaries->filter(fn($divisionSla) => is_array($divisionSla)) as $divisionSla)
                                    <div class="flex justify-between gap-3">
                                        <span class="text-gray-300">{{ $divisionSla['division_name'] ?? '-' }}</span>
                                        <span class="text-emerald-300">
                                            {{ ($divisionSla['sla_percentage'] ?? null) === null ? 'Belum tersedia' : \App\Models\ProjectTask::formatSlaPercentage($divisionSla['sla_percentage']) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <p class="text-xs text-gray-500 mt-4">
                    Rumus: rata-rata SLA task per divisi, lalu dirata-ratakan antar divisi proyek.
                </p>
            </div>

            {{-- PROGRESS TIMELINE LIST --}}
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">📅 Progress Timeline</h3>
                
                @if(isset($workflowSteps) && count($workflowSteps) > 0)
                <div class="space-y-3">
                    @foreach($workflowSteps as $step)
                    <div class="flex items-center justify-between p-3 rounded-lg 
                        @if($step['status'] === 'ongoing') bg-blue-500/10 border border-blue-500/30
                        @elseif($step['status'] === 'completed') bg-green-500/10 border border-green-500/20
                        @else bg-gray-700/50 @endif">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full
                                @if($step['status'] === 'completed') bg-green-500
                                @elseif($step['status'] === 'ongoing') bg-blue-500 animate-pulse
                                @else bg-gray-500 @endif"></div>
                            <div>
                                <p class="text-sm font-medium text-white">{{ $step['label'] }}</p>
                                <p class="text-xs text-gray-400">{{ $step['caption'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs px-2 py-1 rounded
                                @if($step['status'] === 'completed') bg-green-500/20 text-green-400
                                @elseif($step['status'] === 'ongoing') bg-blue-500/20 text-blue-400
                                @else bg-gray-500/20 text-gray-400 @endif">
                                {{ $step['status'] === 'completed' ? 'Selesai' : ($step['status'] === 'ongoing' ? 'Berjalan' : 'Menunggu') }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-400 text-center py-4">
                    Timeline belum tersedia.
                </p>
                @endif
            </div>

            {{-- PROJECT INFO --}}
            <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">ℹ️ Info Proyek</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Kategori</span>
                        <span class="text-white">{{ ucfirst($project->category) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Customer</span>
                        <span class="text-white">{{ $project->customer?->company ?? '-' }}</span>
                    </div>
                    @if($project->start_date)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Mulai</span>
                        <span class="text-white">{{ $project->start_date->format('d M Y') }}</span>
                    </div>
                    @endif
                    @if($project->deadline)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Deadline</span>
                        <span class="text-white">{{ $project->deadline->format('d M Y') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-400">SLA Proyek</span>
                        <span class="text-white">{{ $project->sla_percentage_formatted }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
