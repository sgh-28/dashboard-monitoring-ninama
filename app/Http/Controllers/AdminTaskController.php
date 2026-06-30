<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\MilestoneService;
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
        $tasks = ProjectTask::with(['project', 'assignee', 'division'])
            ->orderByDesc('deadline')
            ->paginate(15);
        
        return view('admin.tasks.index', compact('tasks'));
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
        ]);

        $project = Project::findOrFail($request->project_id);
        $divisionId = $request->division_id;
        $division = ProjectDivision::where('project_id', $project->id)->findOrFail($divisionId);
        
        $assignee = User::where('jabatan', $division->name)
            ->where('bidang', $project->category)
            ->whereHas('role', function($q) {
                $q->where('name', 'pegawai');
            })
            ->first();
        
        if (!$assignee) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Tidak ada pegawai untuk divisi: {$division->name}");
        }

        $tasksCreated = 0;
        foreach ($request->tasks ?? [] as $taskData) {
            if (empty($taskData['title']) || empty($taskData['deadline'])) {
                continue;
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
                'sla_target' => $taskData['sla_target'] ?? 100,
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
        $projects = Project::all();
        $users = User::whereHas('role', function($q) {
                $q->where('name', 'pegawai');
            })
            ->with('role')
            ->get();
        
        return view('admin.tasks.edit', compact('task', 'projects', 'users'));
    }

    public function update(Request $request, ProjectTask $task)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'division_id' => 'nullable|exists:project_divisions,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'required|exists:users,id',
            'deadline' => 'required|date',
            'status' => 'required|in:pending,ongoing,done',
            'sla_target' => 'nullable|integer|min:0|max:100',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'delay_reason' => 'nullable|string',
        ]);

        $task->update($validated);

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
}
