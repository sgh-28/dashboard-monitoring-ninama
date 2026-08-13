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

    @php
        $allTasksDone = $project->tasks->isNotEmpty() && $project->tasks->every(fn($task) => $task->status === 'done');
        $canGiveFeedback = $project->status === 'done' && $allTasksDone && !$project->customer_feedback_submitted_at;
        $ratingLabels = [
            1 => 'Sangat Tidak Puas',
            2 => 'Tidak Puas',
            3 => 'Cukup Puas',
            4 => 'Puas',
            5 => 'Sangat Puas',
        ];
    @endphp

    @if($project->status === 'done' && $allTasksDone)
        <div class="mt-6 bg-gray-800 border border-gray-700 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-white">Feedback Customer</h2>

            @if($project->customer_feedback_submitted_at)
                <p class="mt-1 text-sm text-gray-400">Feedback sudah dikirim dan tidak dapat diubah kembali.</p>

                <div class="mt-5 rounded-lg border border-gray-700 bg-gray-900/40 p-4">
                    <p class="text-sm text-gray-400">Bagaimana pendapat Anda mengenai hasil pengerjaan proyek yang telah diselesaikan?</p>
                    <p class="mt-2 whitespace-pre-line text-gray-200">{{ $project->customer_feedback }}</p>
                </div>

                <div class="mt-4 rounded-lg border border-gray-700 bg-gray-900/40 p-4">
                    <p class="text-sm text-gray-400">Bagaimana tingkat kepuasan Anda terhadap hasil pengerjaan proyek ini?</p>
                    <p class="mt-2 text-lg font-semibold text-yellow-300">
                        {{ str_repeat('⭐', (int) $project->customer_satisfaction_rating) }}
                        <span class="ml-2 text-sm text-gray-300">{{ $ratingLabels[(int) $project->customer_satisfaction_rating] ?? '-' }}</span>
                    </p>
                    <p class="mt-2 text-xs text-gray-500">Dikirim pada {{ $project->customer_feedback_submitted_at->format('d/m/Y H:i') }}</p>
                </div>
            @else
                <p class="mt-1 text-sm text-gray-400">Silakan berikan penilaian setelah proyek dinyatakan selesai.</p>

                <form method="POST" action="{{ route('customer.projects.feedback.store', $project) }}" class="mt-5 space-y-5">
                    @csrf

                    <div>
                        <label for="customer_feedback" class="block text-sm font-medium text-gray-300">
                            Bagaimana pendapat Anda mengenai hasil pengerjaan proyek yang telah diselesaikan?
                        </label>
                        <textarea
                            id="customer_feedback"
                            name="customer_feedback"
                            rows="5"
                            required
                            class="mt-2 w-full rounded-lg border border-gray-600 bg-gray-700 px-4 py-3 text-white placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Tuliskan keterangan atau review Anda..."
                        >{{ old('customer_feedback') }}</textarea>
                        @error('customer_feedback')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <p class="block text-sm font-medium text-gray-300">
                            Bagaimana tingkat kepuasan Anda terhadap hasil pengerjaan proyek ini?
                        </p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-5">
                            @foreach($ratingLabels as $rating => $label)
                                <label class="cursor-pointer rounded-lg border border-gray-700 bg-gray-900/40 p-3 transition hover:border-yellow-400/60">
                                    <input
                                        type="radio"
                                        name="customer_satisfaction_rating"
                                        value="{{ $rating }}"
                                        class="sr-only peer"
                                        required
                                        {{ (string) old('customer_satisfaction_rating') === (string) $rating ? 'checked' : '' }}
                                    >
                                    <span class="block rounded-md p-2 text-center peer-checked:bg-yellow-500/15 peer-checked:ring-2 peer-checked:ring-yellow-400">
                                        <span class="block text-lg">{{ str_repeat('⭐', $rating) }}</span>
                                        <span class="mt-1 block text-xs text-gray-300">{{ $label }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('customer_satisfaction_rating')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 font-medium text-white transition hover:bg-blue-700">
                        Simpan Feedback
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>
@endsection
