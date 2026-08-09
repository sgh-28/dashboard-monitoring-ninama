<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectTask;
use Illuminate\Support\Collection;

class ProjectProgressService
{
    public static function projectProgress(Project $project): float
    {
        $tasks = self::projectTasks($project);
        $totalTasks = $tasks->count();

        if ($totalTasks === 0) {
            return 0.0;
        }

        return self::percentage($tasks->where('status', 'done')->count(), $totalTasks);
    }

    public static function divisionProgress(ProjectDivision $division): float
    {
        $tasks = self::divisionTasks($division);
        $totalTasks = $tasks->count();

        if ($totalTasks === 0) {
            return 0.0;
        }

        return self::percentage($tasks->where('status', 'done')->count(), $totalTasks);
    }

    public static function syncProject(int|Project $project): void
    {
        $project = $project instanceof Project
            ? $project
            : Project::query()->find($project);

        if (!$project) {
            return;
        }

        $project->loadMissing(['divisions.tasks', 'tasks']);

        foreach ($project->divisions as $division) {
            $progress = self::divisionProgress($division);

            if ((float) $division->progress !== $progress) {
                $division->forceFill(['progress' => $progress])->saveQuietly();
            }
        }

        $projectProgress = self::projectProgress($project);

        if ((float) $project->progress !== $projectProgress) {
            $project->forceFill(['progress' => $projectProgress])->saveQuietly();
        }
    }

    private static function percentage(int $done, int $total): float
    {
        return round(($done / $total) * 100, 2);
    }

    private static function projectTasks(Project $project): Collection
    {
        if ($project->relationLoaded('tasks')) {
            return $project->tasks;
        }

        if (!$project->exists) {
            return collect();
        }

        return $project->tasks()->get();
    }

    private static function divisionTasks(ProjectDivision $division): Collection
    {
        if ($division->relationLoaded('tasks')) {
            return $division->tasks;
        }

        if (!$division->exists) {
            return collect();
        }

        return $division->tasks()->get();
    }
}
