<?php

namespace App\Services;

use App\Models\ProjectTask;
use App\Models\ProjectMilestone;
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

        Log::info("Milestones generated for project {$projectId}");
    }

    /**
     * Update status milestone berdasarkan task yang selesai
     */
    public function updateMilestoneStatus(ProjectTask $task)
    {
        $milestones = ProjectMilestone::where('project_id', $task->project_id)
            ->where('status', '!=', 'completed')
            ->get();

        foreach ($milestones as $milestone) {
            // Cek apakah semua task untuk milestone ini sudah selesai
            $relatedTasks = ProjectTask::where('project_id', $task->project_id)
                ->where('division_id', $task->division_id)
                ->get();

            $allCompleted = $relatedTasks->every(fn($t) => $t->status === 'done');

            if ($allCompleted && $milestone->status !== 'completed') {
                $milestone->update([
                    'status' => 'completed',
                    'actual_date' => now(),
                ]);
            }
        }
    }
}