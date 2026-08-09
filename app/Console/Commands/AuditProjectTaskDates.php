<?php

namespace App\Console\Commands;

use App\Models\ProjectTask;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AuditProjectTaskDates extends Command
{
    protected $signature = 'projects:audit-task-dates';

    protected $description = 'Audit read-only task yang berada di luar periode proyek.';

    public function handle(): int
    {
        $tasks = ProjectTask::with('project')->orderBy('project_id')->orderBy('id')->get();
        $rows = [];

        foreach ($tasks as $task) {
            $project = $task->project;

            if (!$project?->start_date || !$project?->deadline) {
                $rows[] = [$project?->id, $project?->name, $task->id, $task->title, 'Periode proyek belum lengkap'];
                continue;
            }

            $projectStart = Carbon::parse($project->start_date)->startOfDay();
            $projectEnd = Carbon::parse($project->deadline)->startOfDay();
            $taskStart = $task->planned_start_date ? Carbon::parse($task->planned_start_date)->startOfDay() : null;
            $taskEnd = $task->deadline ? Carbon::parse($task->deadline)->startOfDay() : null;

            $issues = [];

            if (!$taskStart) {
                $issues[] = 'Tanggal mulai task kosong';
            } elseif ($taskStart->lt($projectStart) || $taskStart->gt($projectEnd)) {
                $issues[] = 'Tanggal mulai di luar periode proyek';
            }

            if (!$taskEnd) {
                $issues[] = 'Deadline task kosong';
            } elseif ($taskEnd->lt($projectStart) || $taskEnd->gt($projectEnd)) {
                $issues[] = 'Deadline task di luar periode proyek';
            }

            if ($taskStart && $taskEnd && $taskEnd->lt($taskStart)) {
                $issues[] = 'Deadline task lebih awal dari tanggal mulai';
            }

            if ($issues) {
                $rows[] = [
                    $project->id,
                    $project->name,
                    $task->id,
                    $task->title,
                    implode('; ', $issues),
                ];
            }
        }

        if (!$rows) {
            $this->info('Tidak ditemukan task di luar periode proyek.');
            return self::SUCCESS;
        }

        $this->table(['Project ID', 'Project', 'Task ID', 'Task', 'Temuan'], $rows);

        return self::SUCCESS;
    }
}
