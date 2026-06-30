<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\ProjectMilestone;
use App\Models\ProjectPhase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MilestoneService
{
    /**
     * Generate milestones otomatis dari daftar task
     */
    public function generateMilestonesFromTasks(int $projectId)
    {
        $tasks = ProjectTask::where('project_id', $projectId)
            ->orderBy('planned_end_date')
            ->get();

        if ($tasks->isEmpty()) {
            return;
        }

        // Hapus milestones lama untuk project ini
        ProjectMilestone::where('project_id', $projectId)->delete();

        // Group tasks by divisi untuk buat milestone per divisi
        $tasksByDivision = $tasks->groupBy('division_id');

        foreach ($tasksByDivision as $divisionId => $divisionTasks) {
            $divisionName = $divisionTasks->first()->division->name ?? 'Divisi';
            
            // Buat milestone untuk setiap divisi
            $lastTask = $divisionTasks->sortByDesc('planned_end_date')->first();
            
            ProjectMilestone::create([
                'project_id' => $projectId,
                'title' => "Tahap {$divisionName} Selesai",
                'description' => "Semua task untuk divisi {$divisionName} telah selesai",
                'planned_date' => $lastTask->planned_end_date,
                'status' => 'pending',
            ]);
        }

        // Buat milestone akhir proyek
        $lastTaskOverall = $tasks->sortByDesc('planned_end_date')->first();
        
        ProjectMilestone::create([
            'project_id' => $projectId,
            'title' => 'Proyek Selesai',
            'description' => 'Seluruh task proyek telah diselesaikan',
            'planned_date' => $lastTaskOverall->planned_end_date,
            'status' => 'pending',
        ]);

        $this->syncProjectMilestoneStatuses($projectId);

        Log::info("Milestones generated for project {$projectId}");
    }

    public function syncProjectMilestoneStatuses(int $projectId): void
    {
        $tasks = ProjectTask::with('division')
            ->where('project_id', $projectId)
            ->get();

        foreach ($tasks->groupBy('division_id') as $divisionTasks) {
            $divisionName = $divisionTasks->first()->division?->name;

            if (!$divisionName) {
                continue;
            }

            $milestone = ProjectMilestone::where('project_id', $projectId)
                ->where('title', "Tahap {$divisionName} Selesai")
                ->first();

            if ($milestone) {
                $this->syncMilestoneFromTasks($milestone, $divisionTasks);
            }
        }

        $projectMilestone = ProjectMilestone::where('project_id', $projectId)
            ->where('title', 'Proyek Selesai')
            ->first();

        if ($projectMilestone) {
            $this->syncMilestoneFromTasks($projectMilestone, $tasks);
        }
    }

    /**
     * Update status milestone berdasarkan task yang selesai
     */
    public function updateMilestoneStatus(ProjectTask $task)
    {
        $task->loadMissing('division');

        $divisionTasks = ProjectTask::where('project_id', $task->project_id)
            ->where('division_id', $task->division_id)
            ->get();

        $divisionMilestone = ProjectMilestone::where('project_id', $task->project_id)
            ->where('title', "Tahap {$task->division?->name} Selesai")
            ->first();

        if ($divisionMilestone) {
            $this->syncMilestoneFromTasks($divisionMilestone, $divisionTasks);
        }

        $projectTasks = ProjectTask::where('project_id', $task->project_id)->get();
        $projectMilestone = ProjectMilestone::where('project_id', $task->project_id)
            ->where('title', 'Proyek Selesai')
            ->first();

        if ($projectMilestone) {
            $this->syncMilestoneFromTasks($projectMilestone, $projectTasks);
        }
    }

    private function syncMilestoneFromTasks(ProjectMilestone $milestone, $tasks): void
    {
        if ($tasks->isEmpty()) {
            return;
        }

        $allCompleted = $tasks->every(fn($task) => $task->status === 'done');
        $actualDate = $tasks
            ->map(fn($task) => $task->actual_end_date ?? $task->completed_at)
            ->filter()
            ->max();

        if ($allCompleted) {
            $milestone->update([
                'status' => $actualDate && Carbon::parse($actualDate)->gt($milestone->planned_date) ? 'delayed' : 'completed',
                'actual_date' => $actualDate ? Carbon::parse($actualDate)->toDateString() : now()->toDateString(),
            ]);

            return;
        }

        $milestone->update([
            'status' => $milestone->planned_date && now()->startOfDay()->gt($milestone->planned_date) ? 'delayed' : 'ongoing',
            'actual_date' => null,
        ]);
    }

    public function buildProjectTimeline(Project $project): array
    {
        $project->loadMissing(['tasks.division', 'tasks.assignee']);

        $tasks = $project->tasks
            ->sortBy(fn($task) => optional($task->planned_start_date ?? $task->created_at)->timestamp ?? 0)
            ->values();

        $projectStart = $project->start_date
            ?? $tasks->pluck('planned_start_date')->filter()->min()
            ?? $tasks->pluck('created_at')->filter()->min()
            ?? now();

        $projectEnd = $project->deadline
            ?? $tasks->pluck('planned_end_date')->filter()->max()
            ?? $tasks->pluck('deadline')->filter()->max()
            ?? now();

        $start = Carbon::parse($projectStart)->startOfDay();
        $end = Carbon::parse($projectEnd)->startOfDay();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        $totalDays = max(1, $start->diffInDays($end));
        $months = $this->buildMonthMarkers($start, $end, $totalDays);
        $alerts = [];

        $phaseTemplates = ProjectPhase::phaseTemplates()[$project->category] ?? [];
        $genericTaskNames = ['Analisis Kebutuhan', 'Desain UI/UX', 'Development', 'Testing', 'Deployment'];

        $items = $tasks->map(function (ProjectTask $task, int $index) use ($start, $totalDays, &$alerts, $phaseTemplates, $genericTaskNames) {
            $plannedStart = $task->planned_start_date
                ? Carbon::parse($task->planned_start_date)
                : ($task->project?->start_date ? Carbon::parse($task->project->start_date) : Carbon::parse($task->created_at));
            $plannedEnd = $task->planned_end_date
                ? Carbon::parse($task->planned_end_date)
                : ($task->deadline ? Carbon::parse($task->deadline) : $plannedStart->copy());

            $actualStart = $task->actual_start_date
                ? Carbon::parse($task->actual_start_date)
                : ($task->status !== 'pending' ? Carbon::parse($task->created_at) : null);
            $actualEnd = $task->actual_end_date
                ? Carbon::parse($task->actual_end_date)
                : ($task->completed_at ? Carbon::parse($task->completed_at) : null);

            $comparisonDate = $actualEnd ?? ($task->status === 'done' ? Carbon::parse($task->updated_at) : now());
            $isDelayed = $plannedEnd && $comparisonDate->gt($plannedEnd) && $task->status !== 'pending';
            $delayDays = $isDelayed ? $plannedEnd->diffInDays($comparisonDate) : 0;

            if ($isDelayed) {
                $alerts[] = [
                    'task' => $this->displayTaskTitle($task, $index, $phaseTemplates, $genericTaskNames),
                    'division' => $this->displayTaskDivision($task, $index, $phaseTemplates),
                    'planned_date' => $plannedEnd->format('d M Y'),
                    'actual_date' => $actualEnd ? $actualEnd->format('d M Y') : null,
                    'delay_days' => $delayDays,
                    'status' => $task->status_label,
                ];
            }

            return [
                'id' => $task->id,
                'title' => $this->displayTaskTitle($task, $index, $phaseTemplates, $genericTaskNames),
                'division' => $this->displayTaskDivision($task, $index, $phaseTemplates),
                'assignee' => $task->assignee?->name,
                'status' => $task->status,
                'status_label' => $task->status_label,
                'color' => $this->timelineColor($index),
                'planned' => $this->barPosition($plannedStart, $plannedEnd, $start, $totalDays),
                'actual' => $actualStart ? $this->barPosition($actualStart, $actualEnd ?? now(), $start, $totalDays) : null,
                'planned_start' => $plannedStart->format('d M Y'),
                'planned_end' => $plannedEnd->format('d M Y'),
                'actual_start' => $actualStart?->format('d M Y'),
                'actual_end' => $actualEnd?->format('d M Y'),
                'is_delayed' => $isDelayed,
                'delay_days' => $delayDays,
            ];
        })->values();

        return [
            'start' => $start,
            'end' => $end,
            'months' => $months,
            'items' => $items,
            'alerts' => $alerts,
        ];
    }

    private function buildMonthMarkers(Carbon $start, Carbon $end, int $totalDays): array
    {
        $markers = [];
        $cursor = $start->copy()->startOfMonth();

        while ($cursor->lte($end)) {
            $markerDate = $cursor->lt($start) ? $start->copy() : $cursor->copy();
            $left = min(100, max(0, ($start->diffInDays($markerDate) / $totalDays) * 100));

            $markers[] = [
                'label' => $markerDate->format('M Y'),
                'left' => $left,
            ];

            $cursor->addMonth();
        }

        return $markers;
    }

    private function barPosition(Carbon $itemStart, Carbon $itemEnd, Carbon $timelineStart, int $totalDays): array
    {
        if ($itemEnd->lt($itemStart)) {
            $itemEnd = $itemStart->copy();
        }

        $left = min(100, max(0, ($timelineStart->diffInDays($itemStart, false) / $totalDays) * 100));
        $width = max(3, ($itemStart->diffInDays($itemEnd) / $totalDays) * 100);

        return [
            'left' => $left,
            'width' => min(100 - $left, max(3, $width)),
        ];
    }

    private function timelineColor(int $index): string
    {
        $colors = ['#f59e0b', '#2563eb', '#059669', '#7c3aed', '#dc2626', '#0891b2', '#db2777'];

        return $colors[$index % count($colors)];
    }

    private function displayTaskTitle(ProjectTask $task, int $index, array $phaseTemplates, array $genericTaskNames): string
    {
        if (isset($phaseTemplates[$index]) && in_array($task->title, $genericTaskNames, true)) {
            return $phaseTemplates[$index]['name'];
        }

        return $task->title;
    }

    private function displayTaskDivision(ProjectTask $task, int $index, array $phaseTemplates): string
    {
        if (isset($phaseTemplates[$index])) {
            return $phaseTemplates[$index]['division'];
        }

        return $task->division?->name ?? 'Tanpa divisi';
    }
}
