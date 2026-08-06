<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\ProjectTask;
use PHPUnit\Framework\TestCase;

class ProjectSlaTest extends TestCase
{
    public function test_it_calculates_sla_percentage_from_on_time_done_tasks(): void
    {
        $project = $this->projectWithTasks([
            $this->task('done', '2026-07-10', '2026-07-10 23:59:00'),
            $this->task('done', '2026-07-10', '2026-07-09 08:00:00'),
            $this->task('done', '2026-07-10', '2026-07-09 08:00:00'),
            $this->task('done', '2026-07-10', '2026-07-09 08:00:00'),
            $this->task('done', '2026-07-10', '2026-07-09 08:00:00'),
            $this->task('done', '2026-07-10', '2026-07-09 08:00:00'),
            $this->task('done', '2026-07-10', '2026-07-09 08:00:00'),
            $this->task('done', '2026-07-10', '2026-07-11 00:00:00'),
        ]);

        $this->assertSame(8, $project->total_tasks_count);
        $this->assertSame(7, $project->on_time_tasks_count);
        $this->assertSame(1, $project->late_tasks_count);
        $this->assertSame(87.5, $project->sla_percentage);
        $this->assertSame('87,5%', $project->sla_percentage_formatted);
    }

    public function test_it_returns_one_hundred_percent_when_all_tasks_are_on_time(): void
    {
        $project = $this->projectWithTasks([
            $this->task('done', '2026-07-10', '2026-07-10'),
            $this->task('done', '2026-07-12', '2026-07-11'),
        ]);

        $this->assertSame(100.0, $project->sla_percentage);
        $this->assertSame('100%', $project->sla_percentage_formatted);
    }

    public function test_it_returns_zero_when_no_tasks_are_on_time(): void
    {
        $project = $this->projectWithTasks([
            $this->task('done', '2026-07-10', '2026-07-11'),
            $this->task('ongoing', '2026-07-10', null),
            $this->task('pending', '2026-07-10', null),
        ]);

        $this->assertSame(0.0, $project->sla_percentage);
        $this->assertSame('0%', $project->sla_percentage_formatted);
    }

    public function test_it_returns_zero_for_project_without_tasks(): void
    {
        $project = $this->projectWithTasks([]);

        $this->assertSame(0, $project->total_tasks_count);
        $this->assertSame(0.0, $project->sla_percentage);
        $this->assertSame('0%', $project->sla_percentage_formatted);
    }

    public function test_same_date_as_deadline_counts_as_on_time(): void
    {
        $project = $this->projectWithTasks([
            $this->task('done', '2026-07-10', '2026-07-10 23:59:00'),
        ]);

        $this->assertTrue($project->tasks->first()->isCompletedOnTime());
        $this->assertSame(100.0, $project->sla_percentage);
    }

    public function test_completed_after_deadline_is_not_on_time(): void
    {
        $project = $this->projectWithTasks([
            $this->task('done', '2026-07-10', '2026-07-11 00:00:00'),
        ]);

        $this->assertFalse($project->tasks->first()->isCompletedOnTime());
        $this->assertSame(0.0, $project->sla_percentage);
    }

    public function test_unfinished_or_null_date_tasks_are_not_on_time(): void
    {
        $project = $this->projectWithTasks([
            $this->task('ongoing', '2026-07-10', null),
            $this->task('done', null, '2026-07-10'),
            $this->task('done', '2026-07-10', null),
        ]);

        $this->assertSame(0, $project->on_time_tasks_count);
        $this->assertSame(0.0, $project->sla_percentage);
    }

    private function projectWithTasks(array $tasks): Project
    {
        $project = new Project();
        $project->setRelation('tasks', collect($tasks));

        return $project;
    }

    private function task(string $status, ?string $deadline, ?string $completedAt): ProjectTask
    {
        return new ProjectTask([
            'status' => $status,
            'deadline' => $deadline,
            'completed_at' => $completedAt,
        ]);
    }
}
