<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectTask;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectSlaTest extends TestCase
{
    use RefreshDatabase;

    private Role $pegawaiRole;
    private User $pm;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-07-10 08:00:00');
        $this->pegawaiRole = Role::create(['name' => 'pegawai']);
        $this->pm = User::factory()->create([
            'role_id' => $this->pegawaiRole->id,
            'bidang' => 'web',
            'jabatan' => 'Project Management',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_project_without_tasks_has_unavailable_sla_and_is_not_final(): void
    {
        $project = $this->project();

        $this->assertSame(0, $project->total_tasks_count);
        $this->assertNull($project->project_sla_percentage);
        $this->assertSame('Belum tersedia', $project->sla_percentage_formatted);
        $this->assertFalse($project->sla_is_final);
    }

    public function test_unfinished_task_before_deadline_has_no_sla_point(): void
    {
        Carbon::setTestNow('2026-07-03 08:00:00');
        $task = $this->task(status: 'ongoing', start: '2026-07-01', deadline: '2026-07-05');

        $this->assertNull($task->task_sla_percentage);
        $this->assertSame('on_track', $task->sla_evaluation_status);
    }

    public function test_task_completed_on_time_has_one_hundred_percent_sla(): void
    {
        $task = $this->task(status: 'done', start: '2026-07-01', deadline: '2026-07-05', completed: '2026-07-05');

        $this->assertSame(100.0, $task->task_sla_percentage);
        $this->assertSame('completed_on_time', $task->sla_evaluation_status);
    }

    public function test_target_five_days_late_one_day_has_eighty_three_point_thirty_three_percent_sla(): void
    {
        $task = $this->task(status: 'done', start: '2026-07-01', deadline: '2026-07-05', completed: '2026-07-06');

        $this->assertSame(5, $task->sla_target_days);
        $this->assertSame(1, $task->late_days);
        $this->assertSame(83.33, $task->task_sla_percentage);
    }

    public function test_target_five_days_late_two_days_has_seventy_one_point_forty_three_percent_sla(): void
    {
        $task = $this->task(status: 'done', start: '2026-07-01', deadline: '2026-07-05', completed: '2026-07-07');

        $this->assertSame(2, $task->late_days);
        $this->assertSame(71.43, $task->task_sla_percentage);
        $this->assertSame('71,43%', $task->task_sla_percentage_formatted);
    }

    public function test_overdue_unfinished_task_has_temporary_sla(): void
    {
        Carbon::setTestNow('2026-07-07 08:00:00');
        $task = $this->task(status: 'ongoing', start: '2026-07-01', deadline: '2026-07-05');

        $this->assertSame('breached', $task->sla_evaluation_status);
        $this->assertSame(2, $task->late_days);
        $this->assertSame(71.43, $task->task_sla_percentage);
    }

    public function test_task_without_start_or_deadline_has_unavailable_sla(): void
    {
        $task = ProjectTask::create([
            'project_id' => $this->project()->id,
            'title' => 'Incomplete schedule',
            'status' => 'done',
            'completed_at' => '2026-07-05 10:00:00',
            'actual_end_date' => '2026-07-05',
        ]);

        $this->assertNull($task->task_sla_percentage);
        $this->assertSame('not_available', $task->sla_evaluation_status);
    }

    public function test_division_sla_uses_each_evaluable_task_once(): void
    {
        $project = $this->project();
        $division = $this->division($project);

        $this->task(project: $project, division: $division, status: 'done', start: '2026-07-01', deadline: '2026-07-05', completed: '2026-07-05');
        $this->task(project: $project, division: $division, status: 'done', start: '2026-07-01', deadline: '2026-07-05', completed: '2026-07-06');
        $this->task(project: $project, division: $division, status: 'done', start: '2026-07-01', deadline: '2026-07-05', completed: '2026-07-05');

        $this->assertSame(94.44, $division->fresh('tasks')->division_sla_percentage);
    }

    public function test_project_sla_is_weighted_by_task_not_plain_average_of_divisions(): void
    {
        $project = $this->project();
        $ui = $this->division($project, 'UI/UX');
        $devops = $this->division($project, 'DevOps');

        $this->task(project: $project, division: $ui, status: 'done', start: '2026-07-01', deadline: '2026-07-05', completed: '2026-07-05');
        $this->task(project: $project, division: $ui, status: 'done', start: '2026-07-01', deadline: '2026-07-05', completed: '2026-07-05');
        $this->task(project: $project, division: $ui, status: 'done', start: '2026-07-01', deadline: '2026-07-05', completed: '2026-07-05');
        $this->task(project: $project, division: $devops, status: 'done', start: '2026-07-01', deadline: '2026-07-05', completed: '2026-07-10');

        $this->assertSame(87.5, $project->fresh(['tasks', 'divisions.tasks'])->project_sla_percentage);
    }

    public function test_progress_one_hundred_can_have_sla_below_one_hundred(): void
    {
        $project = $this->project();
        $division = $this->division($project);
        $this->task(project: $project, division: $division, status: 'done', start: '2026-07-01', deadline: '2026-07-05', completed: '2026-07-06');

        $project = $project->fresh(['tasks', 'divisions.tasks']);

        $this->assertSame(100.0, $project->overall_progress);
        $this->assertSame(83.33, $project->project_sla_percentage);
    }

    public function test_sla_not_final_when_task_not_done(): void
    {
        $project = $this->project(['status' => 'done', 'completed_by' => $this->pm->id, 'completed_at' => now()]);
        $this->task(project: $project, status: 'ongoing', start: '2026-07-01', deadline: '2026-07-05');

        $this->assertFalse($project->fresh(['tasks', 'completedBy'])->sla_is_final);
    }

    public function test_sla_not_final_when_task_done_but_not_approved(): void
    {
        $project = $this->project(['status' => 'done', 'completed_by' => $this->pm->id, 'completed_at' => now()]);
        $this->task(project: $project, status: 'done', start: '2026-07-01', deadline: '2026-07-05', completed: '2026-07-05');

        $this->assertFalse($project->fresh(['tasks', 'completedBy'])->sla_is_final);
    }

    public function test_sla_not_final_when_project_not_done(): void
    {
        $project = $this->project(['status' => 'ongoing', 'completed_by' => $this->pm->id, 'completed_at' => now()]);
        $this->approvedDoneTask($project);

        $this->assertFalse($project->fresh(['tasks', 'completedBy'])->sla_is_final);
    }

    public function test_sla_not_final_when_completed_at_empty(): void
    {
        $project = $this->project(['status' => 'done', 'completed_by' => $this->pm->id, 'completed_at' => null]);
        $this->approvedDoneTask($project);

        $this->assertFalse($project->fresh(['tasks', 'completedBy'])->sla_is_final);
    }

    public function test_sla_not_final_when_completed_by_empty(): void
    {
        $project = $this->project(['status' => 'done', 'completed_by' => null, 'completed_at' => now()]);
        $this->approvedDoneTask($project);

        $this->assertFalse($project->fresh(['tasks', 'completedBy'])->sla_is_final);
    }

    public function test_sla_not_final_when_completed_by_is_not_authorized_pm(): void
    {
        $otherPm = User::factory()->create([
            'role_id' => $this->pegawaiRole->id,
            'bidang' => 'internet',
            'jabatan' => 'Project Management',
        ]);
        $project = $this->project(['status' => 'done', 'completed_by' => $otherPm->id, 'completed_at' => now()]);
        $this->approvedDoneTask($project);

        $this->assertFalse($project->fresh(['tasks', 'completedBy'])->sla_is_final);
    }

    public function test_sla_final_when_all_requirements_are_complete(): void
    {
        $project = $this->project(['status' => 'done', 'completed_by' => $this->pm->id, 'completed_at' => now()]);
        $this->approvedDoneTask($project);

        $this->assertTrue($project->fresh(['tasks', 'completedBy'])->sla_is_final);
    }

    private function approvedDoneTask(Project $project): ProjectTask
    {
        return $this->task(
            project: $project,
            status: 'done',
            start: '2026-07-01',
            deadline: '2026-07-05',
            completed: '2026-07-05',
            verificationStatus: 'approved'
        );
    }

    private function project(array $overrides = []): Project
    {
        return Project::create(array_merge([
            'name' => 'Website Test',
            'category' => 'web',
            'status' => 'ongoing',
            'client_name' => 'Customer Test',
            'start_date' => '2026-07-01',
            'deadline' => '2026-07-31',
            'progress' => 0,
            'sla' => 100,
        ], $overrides));
    }

    private function division(Project $project, string $name = 'UI/UX'): ProjectDivision
    {
        return ProjectDivision::create([
            'project_id' => $project->id,
            'name' => $name,
            'progress' => 0,
        ]);
    }

    private function task(
        ?Project $project = null,
        ?ProjectDivision $division = null,
        string $status = 'pending',
        ?string $start = null,
        ?string $deadline = null,
        ?string $completed = null,
        string $verificationStatus = 'pending'
    ): ProjectTask {
        $project ??= $this->project();

        return ProjectTask::create([
            'project_id' => $project->id,
            'division_id' => $division?->id,
            'title' => 'Task Test',
            'status' => $status,
            'verification_status' => $verificationStatus,
            'planned_start_date' => $start,
            'planned_end_date' => $deadline,
            'deadline' => $deadline,
            'completed_at' => $completed ? Carbon::parse($completed)->setTime(10, 0) : null,
            'actual_end_date' => $completed,
            'progress' => $status === 'done' ? 100 : 0,
            'sla_target' => 100,
        ]);
    }
}
