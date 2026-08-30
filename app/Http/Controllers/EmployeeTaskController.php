<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectTaskSubmission;
use App\Services\MilestoneService;
use App\Services\ProjectProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class EmployeeTaskController extends Controller
{
    /**
     * Display task list for the logged-in employee/marketing.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ProjectTask::where('assigned_to', Auth::id())
            ->with(['project', 'division', 'latestSubmission']);

        // Filter status / verification status
        if ($request->status === 'pending_review') {
            $query->where('verification_status', 'pending_review');
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->orderByRaw("CASE 
                WHEN status = 'pending' THEN 1 
                WHEN status = 'ongoing' THEN 2 
                WHEN status = 'done' THEN 3 
                ELSE 4 END")
            ->orderBy('deadline')
            ->paginate(10);

        $isProjectManagement = $this->isProjectManagementUser($user);
        $managedProjects = collect();

        if ($isProjectManagement) {
            $managedProjects = Project::withCount([
                    'tasks',
                    'tasks as completed_tasks_count' => fn($q) => $q->where('status', 'done'),
                    'tasks as approved_tasks_count' => fn($q) => $q->where('verification_status', 'approved'),
                ])
                ->where('category', $user->bidang)
                ->whereHas('divisions', fn($q) => $q->where('name', 'Project Management'))
                ->whereNotIn('status', ['done', 'rejected'])
                ->orderByDesc('updated_at')
                ->get();
        }
        
        return view('employee.tasks.index', compact('tasks', 'managedProjects', 'isProjectManagement'));
    }

    public function showManagedProject(Project $project)
    {
        $user = Auth::user();

        if (!$this->canManageProject($project, $user)) {
            abort(403, 'Anda tidak berhak mengakses proyek ini.');
        }

        $project->load([
            'phases' => fn($q) => $q->orderBy('phase_order'),
            'customer',
            'divisions.tasks',
            'tasks.division',
            'tasks.assignee',
            'tasks.verifier',
            'tasks.submissions.submitter',
            'tasks.submissions.reviewer',
        ]);

        $overallProgress = $project->overall_progress;
        $slaStatus = $project->project_sla_status;
        $timelineData = app(MilestoneService::class)->buildProjectTimeline($project);
        $slaSummary = [
            'on_track' => $project->phases->where('sla_status', 'on_track')->count(),
            'warning' => $project->phases->where('sla_status', 'warning')->count(),
            'breached' => $project->phases->where('sla_status', 'breached')->count(),
        ];
        $canVerifyTasks = true;

        return view('projects.detail', compact('project', 'overallProgress', 'slaStatus', 'slaSummary', 'timelineData', 'canVerifyTasks'));
    }

    /**
     * Show task detail (read-only).
     */
    public function show(ProjectTask $task)
    {
        if ($task->assigned_to !== Auth::id()) {
            abort(403);
        }
        $task->load(['project', 'division', 'submissions.submitter', 'submissions.reviewer']);
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
        $task->load(['project', 'latestSubmission']);
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
                'verification_status' => 'pending',
                'actual_start_date' => $task->actual_start_date ?? now()->toDateString(),
            ]);
            app(MilestoneService::class)->updateMilestoneStatus($task);

            return redirect()->route('employee.tasks.show', $task)
                ->with('success', '🚀 Status task diubah menjadi "Dalam Pengerjaan"!');
        }

        // Submit selesai (dengan bukti)
        $validated = $request->validate([
            'completion_notes' => 'required|string|max:1000',
            'proof_image' => 'required|file|max:5120',
        ], [
            'completion_notes.required' => 'Keterangan pengerjaan wajib diisi.',
            'proof_image.required' => 'Bukti pengerjaan (foto) wajib diupload.',
            'proof_image.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');
            $extension = strtolower($file->getClientOriginalExtension());
            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            if (!in_array($extension, $allowedExtensions, true)) {
                return back()
                    ->withInput()
                    ->withErrors(['proof_image' => 'Format foto harus JPG, JPEG, atau PNG.']);
            }

            $fileName = 'task-' . $task->id . '-' . now()->format('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
            $directory = storage_path('app/public/task_proofs');
            File::ensureDirectoryExists($directory);
            $file->move($directory, $fileName);
            $validated['proof_image'] = 'task_proofs/' . $fileName;
        }

        ProjectTaskSubmission::create([
            'project_task_id' => $task->id,
            'submitted_by' => Auth::id(),
            'proof_image' => $validated['proof_image'] ?? null,
            'completion_notes' => $validated['completion_notes'],
            'status' => 'submitted',
        ]);

        $validated['status'] = 'done';
        $validated['verification_status'] = 'pending_review';
        $validated['verification_notes'] = null;
        $validated['verified_by'] = null;
        $validated['verified_at'] = null;
        $validated['completed_at'] = now();
        $validated['actual_end_date'] = now()->toDateString();
        $validated['progress'] = 90;
        
        $task->update($validated);

        app(MilestoneService::class)->updateMilestoneStatus($task);

        return redirect()->route('employee.tasks.index')
            ->with('success', '📤 Laporan pengerjaan berhasil dikirim!');
    }
    public function approveTask(Request $request, ProjectTask $task)
    {
        $user = Auth::user();

        if (!$this->canManageProject($task->project, $user)) {
            abort(403, 'Anda tidak berhak memverifikasi task ini.');
        }

        if ($task->status !== 'done' || $task->verification_status !== 'pending_review') {
            return back()->with('error', 'Task belum selesai dikirim oleh pegawai.');
        }

        $validated = $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        $latestSubmission = $task->submissions()->first();
        if ($latestSubmission) {
            $latestSubmission->update([
                'status' => 'approved',
                'revision_notes' => null,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);
        }

        $task->update([
            'verification_status' => 'approved',
            'verification_notes' => $validated['verification_notes'] ?? null,
            'verified_by' => $user->id,
            'verified_at' => now(),
            'progress' => 100,
        ]);

        app(MilestoneService::class)->updateMilestoneStatus($task);

        return back()->with('success', "Task {$task->title} berhasil disetujui.");
    }

    public function requestRevision(Request $request, ProjectTask $task)
    {
        $user = Auth::user();

        if (!$this->canManageProject($task->project, $user)) {
            abort(403, 'Anda tidak berhak memverifikasi task ini.');
        }

        if ($task->status !== 'done' || $task->verification_status !== 'pending_review') {
            return back()->with('error', 'Task belum memiliki laporan baru yang dapat direvisi.');
        }

        $validated = $request->validate([
            'revision_notes' => 'required|string|max:1000',
        ], [
            'revision_notes.required' => 'Catatan revisi wajib diisi.',
        ]);

        $latestSubmission = $task->submissions()->first();
        if ($latestSubmission) {
            $latestSubmission->update([
                'status' => 'revision_requested',
                'revision_notes' => $validated['revision_notes'],
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);
        }

        $task->update([
            'status' => 'ongoing',
            'verification_status' => 'revision_requested',
            'verification_notes' => $validated['revision_notes'],
            'verified_by' => $user->id,
            'verified_at' => now(),
            'completed_at' => null,
            'actual_end_date' => null,
            'progress' => 75,
        ]);

        app(MilestoneService::class)->updateMilestoneStatus($task);

        return back()->with('success', "Revisi untuk task {$task->title} berhasil dikirim ke pegawai.");
    }

    public function completeProject(Project $project)
    {
        $user = Auth::user();

        if (!$this->canCompleteProject($project, $user)) {
            abort(403, 'Anda tidak berhak menyelesaikan proyek ini.');
        }

        $totalTasks = $project->tasks()->count();
        $unfinishedTasks = $project->tasks()->where('status', '!=', 'done')->count();
        $unapprovedTasks = $project->tasks()->where('verification_status', '!=', 'approved')->count();

        if ($totalTasks === 0) {
            return back()->with('error', 'Proyek belum memiliki task.');
        }

        if ($unfinishedTasks > 0) {
            return back()->with('error', "Masih ada {$unfinishedTasks} task yang belum selesai.");
        }

        if ($unapprovedTasks > 0) {
            return back()->with('error', "Masih ada {$unapprovedTasks} task yang belum disetujui Project Management.");
        }

        $project->update([
            'status' => 'done',
            'completed_by' => $user->id,
            'completed_at' => now(),
            'end_date' => now()->toDateString(),
            'status_text' => 'Selesai diverifikasi Project Management',
        ]);

        ProjectProgressService::syncProject($project);

        return redirect()->route('employee.tasks.index')
            ->with('success', "Proyek {$project->name} berhasil ditandai selesai.");
    }

    private function isProjectManagementUser($user): bool
    {
        return ($user?->role?->name ?? '') === 'project_manager';
    }

    private function canCompleteProject(Project $project, $user): bool
    {
        return $this->canManageProject($project, $user);
    }

    private function canManageProject(Project $project, $user): bool
    {
        if (!$this->isProjectManagementUser($user)) {
            return false;
        }

        return $project->category === $user->bidang
            && $project->divisions()
                ->where('name', 'Project Management')
            ->exists();
    }
}
