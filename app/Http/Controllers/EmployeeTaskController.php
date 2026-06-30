<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Services\MilestoneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployeeTaskController extends Controller
{
    /**
     * Display task list for the logged-in employee/marketing.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ProjectTask::where('assigned_to', Auth::id())
            ->with(['project', 'division']);

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->orderByRaw("CASE 
                WHEN status = 'pending' THEN 1 
                WHEN status = 'ongoing' THEN 2 
                WHEN status = 'done' THEN 3 
                ELSE 4 END")
            ->orderBy('deadline')
            ->paginate(10);

        $managedProjects = collect();

        if ($this->isProjectManagementUser($user)) {
            $managedProjects = Project::withCount([
                    'tasks',
                    'tasks as completed_tasks_count' => fn($q) => $q->where('status', 'done'),
                ])
                ->whereHas('tasks', function ($q) use ($user) {
                    $q->where('assigned_to', $user->id)
                        ->whereHas('division', fn($division) => $division->where('name', 'Project Management'));
                })
                ->whereNotIn('status', ['done', 'rejected'])
                ->orderByDesc('updated_at')
                ->get();
        }
        
        return view('employee.tasks.index', compact('tasks', 'managedProjects'));
    }

    /**
     * Show task detail (read-only).
     */
    public function show(ProjectTask $task)
    {
        if ($task->assigned_to !== Auth::id()) {
            abort(403);
        }
        return view('employee.tasks.show', compact('task'));
    }

    /**
     * Show form to submit task completion.
     */
    public function submitForm(ProjectTask $task)
    {
        if ($task->assigned_to !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses task ini.');
        }
        return view('employee.tasks.submit', compact('task'));
    }

    /**
     * Handle task submission with proof image.
     */
    public function submit(Request $request, ProjectTask $task)
    {
        if ($task->assigned_to !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // Jika hanya update status (tombol Mulai Kerjakan)
        if ($request->has('status') && $request->status === 'ongoing' && !$request->has('completion_notes')) {
            $task->update([
                'status' => 'ongoing',
                'progress' => 50,
                'actual_start_date' => $task->actual_start_date ?? now()->toDateString(),
            ]);
            app(MilestoneService::class)->updateMilestoneStatus($task);

            return redirect()->route('employee.tasks.show', $task)
                ->with('success', '🚀 Status task diubah menjadi "Dalam Pengerjaan"!');
        }

        // Submit selesai (dengan bukti)
        $validated = $request->validate([
            'completion_notes' => 'required|string|max:1000',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'completion_notes.required' => 'Keterangan pengerjaan wajib diisi.',
            'proof_image.required' => 'Bukti pengerjaan (foto) wajib diupload.',
        ]);

        if ($request->hasFile('proof_image')) {
            if ($task->proof_image && Storage::disk('public')->exists($task->proof_image)) {
                Storage::disk('public')->delete($task->proof_image);
            }
            $path = $request->file('proof_image')->store('task_proofs', 'public');
            $validated['proof_image'] = $path;
        }

        $validated['status'] = 'done';
        $validated['completed_at'] = now();
        $validated['actual_start_date'] = $task->actual_start_date ?? now()->toDateString();
        $validated['actual_end_date'] = now()->toDateString();
        $validated['progress'] = 100;
        
        $task->update($validated);

        app(MilestoneService::class)->updateMilestoneStatus($task);

        return redirect()->route('employee.tasks.index')
            ->with('success', '📤 Laporan pengerjaan berhasil dikirim!');
    }
    public function completeProject(Project $project)
    {
        $user = Auth::user();

        if (!$this->canCompleteProject($project, $user)) {
            abort(403, 'Anda tidak berhak menyelesaikan proyek ini.');
        }

        $totalTasks = $project->tasks()->count();
        $unfinishedTasks = $project->tasks()->where('status', '!=', 'done')->count();

        if ($totalTasks === 0) {
            return back()->with('error', 'Proyek belum memiliki task.');
        }

        if ($unfinishedTasks > 0) {
            return back()->with('error', "Masih ada {$unfinishedTasks} task yang belum selesai.");
        }

        $project->update([
            'status' => 'done',
            'progress' => 100,
            'end_date' => now()->toDateString(),
            'status_text' => 'Selesai diverifikasi Project Management',
        ]);

        return redirect()->route('employee.tasks.index')
            ->with('success', "Proyek {$project->name} berhasil ditandai selesai.");
    }

    private function isProjectManagementUser($user): bool
    {
        return strcasecmp(trim((string) $user?->jabatan), 'Project Management') === 0;
    }

    private function canCompleteProject(Project $project, $user): bool
    {
        if (!$this->isProjectManagementUser($user)) {
            return false;
        }

        return $project->tasks()
            ->where('assigned_to', $user->id)
            ->whereHas('division', fn($q) => $q->where('name', 'Project Management'))
            ->exists();
    }
}
