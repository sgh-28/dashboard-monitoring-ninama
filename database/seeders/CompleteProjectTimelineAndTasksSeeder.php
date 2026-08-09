<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectPhase;
use App\Models\ProjectTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CompleteProjectTimelineAndTasksSeeder extends Seeder
{
    public function run(): void
    {
        $projects = Project::query()
            ->with(['phases', 'divisions', 'tasks'])
            ->whereIn('status', ['ongoing', 'done'])
            ->orderBy('id')
            ->get();

        $createdPhases = 0;
        $createdDivisions = 0;
        $createdTasks = 0;
        $updatedRecords = 0;

        foreach ($projects as $project) {
            $templates = ProjectPhase::phaseTemplates()[$project->category]
                ?? ProjectPhase::phaseTemplates()['web'];

            $projectStart = $project->start_date
                ? Carbon::parse($project->start_date)->startOfDay()
                : Carbon::parse($project->created_at ?? now())->startOfDay();

            $cursor = $projectStart->copy();

            foreach ($templates as $index => $template) {
                $order = $index + 1;
                $phaseStart = $cursor->copy();
                $phaseEnd = $cursor->copy()->addDays(max(1, (int) $template['days']) - 1);
                $cursor = $phaseEnd->copy()->addDay();

                $division = ProjectDivision::firstOrCreate(
                    ['project_id' => $project->id, 'name' => $template['division']],
                    ['progress' => 0]
                );

                if ($division->wasRecentlyCreated) {
                    $createdDivisions++;
                }

                $phase = ProjectPhase::firstOrCreate(
                    ['project_id' => $project->id, 'phase_order' => $order],
                    [
                        'phase_name' => $template['name'],
                        'status' => 'pending',
                        'progress' => 0,
                        'start_date' => $phaseStart->toDateString(),
                        'target_date' => $phaseEnd->toDateString(),
                        'completed_date' => null,
                        'sla_days' => max(1, (int) $template['days']),
                        'actual_days' => null,
                        'sla_status' => 'on_track',
                        'notes' => "Timeline {$template['name']} untuk {$project->name}.",
                    ]
                );

                if ($phase->wasRecentlyCreated) {
                    $createdPhases++;
                } else {
                    $phaseUpdates = [];

                    foreach ([
                        'phase_name' => $template['name'],
                        'start_date' => $phaseStart->toDateString(),
                        'target_date' => $phaseEnd->toDateString(),
                        'sla_days' => max(1, (int) $template['days']),
                    ] as $field => $value) {
                        if (blank($phase->{$field})) {
                            $phaseUpdates[$field] = $value;
                        }
                    }

                    if (!empty($phaseUpdates)) {
                        $phase->update($phaseUpdates);
                        $updatedRecords++;
                    }
                }

                $task = ProjectTask::where('project_id', $project->id)
                    ->where('title', $template['name'])
                    ->first();

                if (!$task) {
                    ProjectTask::create([
                        'project_id' => $project->id,
                        'division_id' => $division->id,
                        'assigned_to' => $this->assigneeId($project->category, $template['division']),
                        'title' => $template['name'],
                        'description' => "Task {$template['name']} untuk proyek {$project->name}.",
                        'deadline' => $phaseEnd->toDateString(),
                        'status' => 'pending',
                        'progress' => 0,
                        'planned_start_date' => $phaseStart->toDateString(),
                        'planned_end_date' => $phaseEnd->toDateString(),
                        'actual_start_date' => null,
                        'actual_end_date' => null,
                        'completed_at' => null,
                        'completion_notes' => null,
                        'verification_status' => 'pending',
                        'sla_target' => 100,
                        'is_notified' => false,
                    ]);

                    $createdTasks++;
                    continue;
                }

                $taskUpdates = [];
                $defaults = [
                    'division_id' => $division->id,
                    'assigned_to' => $this->assigneeId($project->category, $template['division']),
                    'deadline' => $phaseEnd->toDateString(),
                    'planned_start_date' => $phaseStart->toDateString(),
                    'planned_end_date' => $phaseEnd->toDateString(),
                    'sla_target' => 100,
                ];

                foreach ($defaults as $field => $value) {
                    if (blank($task->{$field}) && filled($value)) {
                        $taskUpdates[$field] = $value;
                    }
                }

                if (!empty($taskUpdates)) {
                    $task->update($taskUpdates);
                    $updatedRecords++;
                }
            }
        }

        $this->command?->info("Missing project timeline/task records filled safely. Phases created: {$createdPhases}, divisions created: {$createdDivisions}, tasks created: {$createdTasks}, records updated: {$updatedRecords}.");
    }

    private function assigneeId(string $category, string $division): ?int
    {
        return User::query()
            ->where('bidang', $category)
            ->where('jabatan', $division)
            ->whereHas('role', fn($query) => $query->where('name', 'pegawai'))
            ->value('id');
    }
}
