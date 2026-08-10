<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectTask;
use App\Services\ProjectProgressService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProjectProgressTest extends TestCase
{
    public function test_project_without_tasks_has_zero_progress(): void
    {
        $project = $this->project([]);

        $this->assertSame(0.0, ProjectProgressService::projectProgress($project));
        $this->assertSame(0.0, $project->overall_progress);
    }

    #[DataProvider('progressCases')]
    public function test_project_progress_uses_done_tasks_only(int $doneTasks, float $expected): void
    {
        $tasks = [];

        for ($i = 0; $i < 8; $i++) {
            $tasks[] = $this->task($i < $doneTasks ? 'done' : 'pending');
        }

        $this->assertSame($expected, ProjectProgressService::projectProgress($this->project($tasks)));
    }

    public static function progressCases(): array
    {
        return [
            '0 of 8' => [0, 0.0],
            '1 of 8' => [1, 12.5],
            '2 of 8' => [2, 25.0],
            '4 of 8' => [4, 50.0],
            '6 of 8' => [6, 75.0],
            '8 of 8' => [8, 100.0],
        ];
    }

    public function test_ongoing_task_is_not_counted_as_done(): void
    {
        $project = $this->project([
            $this->task('done'),
            $this->task('ongoing'),
            $this->task('pending'),
            $this->task('pending'),
        ]);

        $this->assertSame(25.0, ProjectProgressService::projectProgress($project));
    }

    public function test_division_progress_uses_only_tasks_in_that_division(): void
    {
        $division = $this->division([
            $this->task('done'),
            $this->task('pending'),
            $this->task('done'),
            $this->task('ongoing'),
        ]);

        $this->assertSame(50.0, ProjectProgressService::divisionProgress($division));
    }

    public function test_adding_and_removing_tasks_recalculates_progress(): void
    {
        $tasks = collect([
            $this->task('done'),
            $this->task('pending'),
        ]);

        $project = new Project();
        $project->setRelation('tasks', $tasks);
        $this->assertSame(50.0, ProjectProgressService::projectProgress($project));

        $project->setRelation('tasks', $tasks->push($this->task('pending')));
        $this->assertSame(33.33, ProjectProgressService::projectProgress($project));

        $project->setRelation('tasks', $tasks->filter(fn($task) => $task->status === 'done')->values());
        $this->assertSame(100.0, ProjectProgressService::projectProgress($project));
    }

    private function project(array $tasks): Project
    {
        $project = new Project();
        $project->setRelation('tasks', collect($tasks));

        return $project;
    }

    private function division(array $tasks): ProjectDivision
    {
        $division = new ProjectDivision();
        $division->setRelation('tasks', collect($tasks));

        return $division;
    }

    private function task(string $status): ProjectTask
    {
        return new ProjectTask(['status' => $status]);
    }
}
