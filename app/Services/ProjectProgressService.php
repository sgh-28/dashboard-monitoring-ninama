<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectDivision;
use Illuminate\Support\Collection;

class ProjectProgressService
{
    public static function projectProgress(Project $project): float
    {
        $divisions = self::projectDivisions($project);
        $totalDivisions = $divisions->count();

        if ($totalDivisions === 0) {
            return 0.0;
        }

        return round($divisions->avg(fn(ProjectDivision $division) => self::divisionProgress($division)), 2);
    }

    public static function divisionProgress(ProjectDivision $division): float
    {
        $tasks = self::divisionTasks($division);
        $totalTasks = $tasks->count();

        if ($totalTasks === 0) {
            return 0.0;
        }

        return round((float) $tasks->avg(fn($task) => (int) ($task->progress ?? 0)), 2);
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

    private static function projectDivisions(Project $project): Collection
    {
        if ($project->relationLoaded('divisions')) {
            return $project->divisions;
        }

        if (!$project->exists) {
            return collect();
        }

        return $project->divisions()->get();
    }
}
