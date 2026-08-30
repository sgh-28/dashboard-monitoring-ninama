<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\MilestoneService;
use App\Services\ProjectProgressService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminTaskController extends Controller
{
    protected $notificationService;
    protected $milestoneService;

    public function __construct(NotificationService $notificationService, MilestoneService $milestoneService)
    {
        $this->notificationService = $notificationService;
        $this->milestoneService = $milestoneService;
    }

    public function index()
    {
        return redirect()->route('admin.projects.index')
            ->with('info', 'Pilih proyek terlebih dahulu untuk mengelola task.');
    }

    public function indexByProject($project_id)
    {
        $project = Project::with(['divisions.tasks.assignee', 'tasks.division', 'tasks.assignee'])->findOrFail($project_id);
        $timelineData = $this->milestoneService->buildProjectTimeline($project);
        
        return view('admin.tasks.index', compact('project', 'timelineData'));
    }

    public function create()
    {
        $project_id = request('project_id');
        
        if (!$project_id) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Pilih proyek terlebih dahulu');
        }
        
        $project = Project::with('divisions')->findOrFail($project_id);
        
        return view('admin.tasks.create', compact('project'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'division_id' => 'required|exists:project_divisions,id',
            'tasks' => 'required|array',
            'tasks.*.title' => 'required|string|max:255',
            'tasks.*.deadline' => 'required|date',
            'tasks.*.planned_start_date' => 'required|date',
            'tasks.*.description' => 'nullable|string',
        ]);

        $project = Project::findOrFail($request->project_id);
        $divisionId = $request->division_id;
        $division = ProjectDivision::where('project_id', $project->id)->findOrFail($divisionId);

        $periodError = $this->validateProjectPeriod($project);
        if ($periodError) {
            return back()->withInput()->withErrors(['project_id' => $periodError]);
        }
        
        $assigneeRole = $division->name === 'Project Management' ? 'project_manager' : 'pegawai';
        $assignee = User::where('jabatan', $division->name)
            ->where('bidang', $project->category)
            ->whereHas('role', function($q) use ($assigneeRole) {
                $q->where('name', $assigneeRole);
            })
            ->first();
        
        if (!$assignee) {
            $accountLabel = $assigneeRole === 'project_manager' ? 'project manager' : 'pegawai';
            return redirect()->back()
                ->withInput()
                ->with('error', "Tidak ada akun {$accountLabel} untuk divisi: {$division->name}");
        }

        $tasksCreated = 0;
        foreach ($request->tasks ?? [] as $index => $taskData) {
            $dateError = $this->validateTaskDates(
                $project,
                $taskData['planned_start_date'] ?? null,
                $taskData['deadline'] ?? null,
                $index
            );

            if ($dateError) {
                return back()->withInput()->withErrors(["tasks.{$index}.deadline" => $dateError]);
            }
            
            $task = ProjectTask::create([
                'project_id' => $project->id,
                'division_id' => $division->id,
                'assigned_to' => $assignee->id,
                'title' => $taskData['title'],
                'description' => $taskData['description'] ?? null,
                'deadline' => $taskData['deadline'],
                'planned_start_date' => $taskData['planned_start_date'] ?? now()->toDateString(),
                'planned_end_date' => $taskData['deadline'],
                // Legacy column: SLA actual dihitung dari tanggal rencana, deadline, dan selesai aktual.
                'sla_target' => 100,
                'status' => 'pending',
            ]);
            
            try {
                $this->notificationService->sendTaskNotification($task);
                Log::info("Notifikasi terkirim untuk task #{$task->id}");
            } catch (\Exception $e) {
                Log::error("Gagal kirim notifikasi: " . $e->getMessage());
            }
            
            $tasksCreated++;
        }
        
        if ($tasksCreated > 0) {
            ProjectProgressService::syncProject($project);

            try {
                $this->milestoneService->generateMilestonesFromTasks($project->id);
            } catch (\Exception $e) {
                Log::error("Gagal generate milestone: " . $e->getMessage());
            }
        }
        
        return redirect()->route('admin.tasks.index.by.project', $project->id)
            ->with('success', "✅ {$tasksCreated} task berhasil dibuat untuk divisi {$division->name}!");
    }

    public function edit(ProjectTask $task)
    {
        $task->load(['project.divisions', 'division']);
        $projects = Project::all();
        $divisions = $task->project?->divisions ?? collect();
        $users = User::whereHas('role', function($q) {
                $q->whereIn('name', ['pegawai', 'project_manager']);
            })
            ->with('role')
            ->get();
        
        return view('admin.tasks.edit', compact('task', 'projects', 'users', 'divisions'));
    }

    public function update(Request $request, ProjectTask $task)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'division_id' => 'required|exists:project_divisions,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'deadline' => 'required|date',
            'status' => 'required|in:pending,ongoing,done',
            'planned_start_date' => 'required|date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'delay_reason' => 'nullable|string',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        $division = ProjectDivision::where('project_id', $project->id)
            ->whereKey($validated['division_id'])
            ->first();

        if (!$division) {
            return back()->withInput()->withErrors(['division_id' => 'Divisi harus berasal dari proyek yang dipilih.']);
        }

        $periodError = $this->validateProjectPeriod($project);
        if ($periodError) {
            return back()->withInput()->withErrors(['project_id' => $periodError]);
        }

        $dateError = $this->validateTaskDates($project, $validated['planned_start_date'], $validated['deadline']);
        if ($dateError) {
            return back()->withInput()->withErrors(['deadline' => $dateError]);
        }

        $assigneeRole = $division->name === 'Project Management' ? 'project_manager' : 'pegawai';
        $assignee = User::whereKey($validated['assigned_to'])
            ->where('bidang', $project->category)
            ->where('jabatan', $division->name)
            ->whereHas('role', fn($q) => $q->where('name', $assigneeRole))
            ->first();

        if (!$assignee) {
            $accountLabel = $assigneeRole === 'project_manager' ? 'project manager' : 'pegawai';
            return back()->withInput()->withErrors([
                'assigned_to' => "Akun {$accountLabel} harus memiliki bidang dan jabatan yang sesuai dengan proyek dan divisi.",
            ]);
        }

        $validated['planned_end_date'] = $validated['deadline'];
        $validated['sla_target'] = 100;

        $task->update($validated);
        ProjectProgressService::syncProject($task->project_id);

        if ($request->has('planned_end_date') || $request->has('deadline')) {
            try {
                $this->milestoneService->generateMilestonesFromTasks($task->project_id);
            } catch (\Exception $e) {
                Log::error("Gagal regenerate milestone: " . $e->getMessage());
            }
        } else {
            $this->milestoneService->syncProjectMilestoneStatuses($task->project_id);
        }

        return redirect()->route('admin.tasks.index.by.project', $task->project_id)
            ->with('success', '✅ Task berhasil diupdate!');
    }

    public function destroy(ProjectTask $task)
    {
        $projectId = $task->project_id;
        $task->delete();
        
        try {
            $this->milestoneService->generateMilestonesFromTasks($projectId);
        } catch (\Exception $e) {
            Log::error("Gagal regenerate milestone: " . $e->getMessage());
        }
        
        return redirect()->route('admin.tasks.index.by.project', $projectId)
            ->with('success', '🗑️ Task berhasil dihapus.');
    }

    private function validateProjectPeriod(Project $project): ?string
    {
        if (!$project->start_date || !$project->deadline) {
            return 'Proyek harus memiliki tanggal mulai dan deadline sebelum task dibuat.';
        }

        return null;
    }

    private function validateTaskDates(Project $project, ?string $taskStart, ?string $taskDeadline, ?int $index = null): ?string
    {
        if (!$taskStart || !$taskDeadline) {
            return 'Tanggal mulai dan deadline task wajib diisi.';
        }

        $projectStart = Carbon::parse($project->start_date)->startOfDay();
        $projectEnd = Carbon::parse($project->deadline)->startOfDay();
        $start = Carbon::parse($taskStart)->startOfDay();
        $deadline = Carbon::parse($taskDeadline)->startOfDay();
        $label = $index === null ? 'Task' : 'Task ke-' . ($index + 1);
        $period = $projectStart->format('d/m/Y') . ' - ' . $projectEnd->format('d/m/Y');

        if ($start->lt($projectStart) || $start->gt($projectEnd)) {
            return "{$label}: tanggal mulai harus berada dalam periode proyek ({$period}).";
        }

        if ($deadline->lt($start)) {
            return "{$label}: deadline task tidak boleh lebih awal dari tanggal mulai.";
        }

        if ($deadline->gt($projectEnd)) {
            return "{$label}: deadline task harus berada dalam periode proyek ({$period}).";
        }

        return null;
    }
}
