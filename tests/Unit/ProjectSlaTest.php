<?php

namespace Tests\Unit;

use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectTask;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class ProjectSlaTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_task_completed_before_deadline_has_one_hundred_percent_sla(): void
    {
        $task = $this->task('done', '2026-07-01', '2026-07-05', '2026-07-04');

        $this->assertSame(100.0, $task->task_sla_percentage);
        $this->assertSame('completed_on_time', $task->sla_evaluation_status);
    }

    public function test_task_completed_on_deadline_has_one_hundred_percent_sla(): void
    {
        $task = $this->task('done', '2026-07-01', '2026-07-05', '2026-07-05 23:59:00');

        $this->assertSame(100.0, $task->task_sla_percentage);
        $this->assertSame('completed_on_time', $task->sla_evaluation_status);
    }

    public function test_target_five_days_and_one_late_day_has_eighty_three_point_thirty_three_percent_sla(): void
    {
        $task = $this->task('done', '2026-07-01', '2026-07-05', '2026-07-06');

        $this->assertSame(5, $task->sla_target_days);
        $this->assertSame(1, $task->late_days);
        $this->assertSame(83.33, $task->task_sla_percentage);
        $this->assertSame('83,33%', $task->task_sla_percentage_formatted);
    }

    public function test_target_five_days_and_two_late_days_has_seventy_one_point_forty_three_percent_sla(): void
    {
        $task = $this->task('done', '2026-07-01', '2026-07-05', '2026-07-07');

        $this->assertSame(2, $task->late_days);
        $this->assertSame(71.43, $task->task_sla_percentage);
    }

    public function test_unfinished_task_before_deadline_has_no_sla_value(): void
    {
        Carbon::setTestNow('2026-07-03');
        $task = $this->task('ongoing', '2026-07-01', '2026-07-05', null);

        $this->assertNull($task->task_sla_percentage);
        $this->assertSame('on_track', $task->sla_evaluation_status);
    }

    public function test_unfinished_task_after_deadline_has_temporary_breached_sla(): void
    {
        Carbon::setTestNow('2026-07-07');
        $task = $this->task('ongoing', '2026-07-01', '2026-07-05', null);

        $this->assertSame('breached', $task->sla_evaluation_status);
        $this->assertSame(2, $task->late_days);
        $this->assertSame(71.43, $task->task_sla_percentage);
    }

    public function test_task_without_complete_schedule_does_not_error_and_is_not_given_one_hundred_percent(): void
    {
        $task = new ProjectTask([
            'status' => 'done',
            'deadline' => '2026-07-05',
            'completed_at' => '2026-07-05',
        ]);

        $this->assertNull($task->task_sla_percentage);
        $this->assertSame('not_available', $task->sla_evaluation_status);
        $this->assertNotSame('100%', $task->task_sla_percentage_formatted);
    }

    public function test_ui_ux_division_with_three_evaluated_tasks_has_weighted_sla_average(): void
    {
        $division = $this->division('UI/UX', [
            $this->task('done', '2026-07-01', '2026-07-05', '2026-07-05'),
            $this->task('done', '2026-07-01', '2026-07-05', '2026-07-06'),
            $this->task('done', '2026-07-01', '2026-07-05', '2026-07-05'),
        ]);

        $this->assertSame(94.44, $division->division_sla_percentage);
        $this->assertSame('94,44%', $division->division_sla_percentage_formatted);
    }

    public function test_division_without_evaluable_tasks_is_unavailable(): void
    {
        Carbon::setTestNow('2026-07-03');
        $division = $this->division('Frontend', [
            $this->task('ongoing', '2026-07-01', '2026-07-05', null),
        ]);

        $this->assertNull($division->division_sla_percentage);
        $this->assertSame('Belum tersedia', $division->division_sla_percentage_formatted);
    }

    public function test_project_sla_is_calculated_from_all_evaluable_task_points(): void
    {
        $project = $this->projectWithDivisions([
            $this->division('UI/UX', [
                $this->task('done', '2026-07-01', '2026-07-05', '2026-07-05'),
                $this->task('done', '2026-07-01', '2026-07-05', '2026-07-06'),
            ]),
            $this->division('Frontend', [
                $this->task('done', '2026-07-01', '2026-07-05', '2026-07-07'),
            ]),
        ]);

        $this->assertSame(84.92, $project->project_sla_percentage);
    }

    public function test_project_sla_uses_task_weight_not_plain_average_of_divisions(): void
    {
        $project = $this->projectWithDivisions([
            $this->division('UI/UX', [
                $this->task('done', '2026-07-01', '2026-07-05', '2026-07-05'),
                $this->task('done', '2026-07-01', '2026-07-05', '2026-07-05'),
                $this->task('done', '2026-07-01', '2026-07-05', '2026-07-05'),
            ]),
            $this->division('DevOps', [
                $this->task('done', '2026-07-01', '2026-07-05', '2026-07-10'),
            ]),
        ]);

        $plainAverage = round((100 + 50) / 2, 2);

        $this->assertSame(87.5, $project->project_sla_percentage);
        $this->assertNotSame($plainAverage, $project->project_sla_percentage);
    }

    public function test_one_task_is_counted_once(): void
    {
        $task = $this->task('done', '2026-07-01', '2026-07-05', '2026-07-05');
        $project = $this->projectWithTasks([$task]);

        $this->assertSame(1, $project->total_tasks_count);
        $this->assertSame(1, $project->evaluated_tasks_count);
        $this->assertSame(100.0, $project->project_sla_percentage);
    }

    public function test_submission_fields_record_completion_date(): void
    {
        $task = $this->task('ongoing', '2026-07-01', '2026-07-05', null);
        $task->fill([
            'status' => 'done',
            'completed_at' => '2026-07-04 10:00:00',
            'actual_end_date' => '2026-07-04',
        ]);

        $this->assertSame('done', $task->status);
        $this->assertSame('2026-07-04', $task->actual_end_date->format('Y-m-d'));
        $this->assertSame(100.0, $task->task_sla_percentage);
    }

    public function test_pm_approval_does_not_change_completion_date_or_task_sla(): void
    {
        $task = $this->task('done', '2026-07-01', '2026-07-05', '2026-07-06');
        $completionDate = $task->completed_at->copy();
        $actualEndDate = $task->actual_end_date->copy();
        $slaBeforeApproval = $task->task_sla_percentage;

        $task->fill([
            'verification_status' => 'approved',
            'verification_notes' => 'Sesuai.',
            'verified_by' => 1,
            'verified_at' => '2026-07-10 09:00:00',
        ]);

        $this->assertTrue($task->completed_at->eq($completionDate));
        $this->assertTrue($task->actual_end_date->eq($actualEndDate));
        $this->assertSame($slaBeforeApproval, $task->task_sla_percentage);
    }

    public function test_progress_one_hundred_can_have_sla_below_one_hundred(): void
    {
        $project = $this->projectWithTasks([
            $this->task('done', '2026-07-01', '2026-07-05', '2026-07-06'),
        ]);
        $project->progress = 100;

        $this->assertSame(100, $project->progress);
        $this->assertSame(83.33, $project->project_sla_percentage);
    }

    public function test_project_without_tasks_does_not_divide_by_zero(): void
    {
        $project = $this->projectWithTasks([]);

        $this->assertSame(0, $project->total_tasks_count);
        $this->assertNull($project->project_sla_percentage);
        $this->assertSame('Belum tersedia', $project->sla_percentage_formatted);
    }

    public function test_sla_is_not_final_when_task_is_not_approved(): void
    {
        $project = $this->projectWithTasks([
            $this->task('done', '2026-07-01', '2026-07-05', '2026-07-05'),
        ]);
        $project->fill(['status' => 'done', 'completed_by' => 10, 'completed_at' => '2026-07-06 08:00:00']);

        $this->assertFalse($project->sla_is_final);
    }

    public function test_sla_is_not_final_when_project_is_not_completed_by_pm(): void
    {
        $task = $this->task('done', '2026-07-01', '2026-07-05', '2026-07-05');
        $task->verification_status = 'approved';
        $project = $this->projectWithTasks([$task]);
        $project->status = 'ongoing';

        $this->assertFalse($project->sla_is_final);
    }

    public function test_sla_is_final_when_all_tasks_done_approved_and_project_completed_by_pm(): void
    {
        $task = $this->task('done', '2026-07-01', '2026-07-05', '2026-07-05');
        $task->verification_status = 'approved';
        $project = $this->projectWithTasks([$task]);
        $project->fill(['status' => 'done', 'completed_by' => 10, 'completed_at' => '2026-07-06 08:00:00']);

        $this->assertTrue($project->sla_is_final);
    }

    public function test_dashboard_values_are_rounded_to_two_decimals(): void
    {
        $project = $this->projectWithTasks([
            $this->task('done', '2026-07-01', '2026-07-03', '2026-07-06'),
        ]);

        $this->assertSame(50.0, $project->project_sla_percentage);
        $this->assertSame('50%', $project->sla_percentage_formatted);
    }

    private function projectWithDivisions(array $divisions): Project
    {
        $project = new Project();
        $project->setRelation('divisions', collect($divisions));
        $project->setRelation('tasks', collect($divisions)->flatMap(fn(ProjectDivision $division) => $division->tasks));

        return $project;
    }

    private function projectWithTasks(array $tasks): Project
    {
        $project = new Project();
        $project->setRelation('tasks', collect($tasks));
        $project->setRelation('divisions', collect());

        return $project;
    }

    private function division(string $name, array $tasks): ProjectDivision
    {
        $division = new ProjectDivision(['name' => $name]);
        $division->setRelation('tasks', collect($tasks));

        return $division;
    }

    private function task(string $status, string $plannedStartDate, string $targetDate, ?string $completedAt): ProjectTask
    {
        return new ProjectTask([
            'status' => $status,
            'planned_start_date' => $plannedStartDate,
            'planned_end_date' => $targetDate,
            'deadline' => $targetDate,
            'completed_at' => $completedAt,
            'actual_end_date' => $completedAt ? Carbon::parse($completedAt)->toDateString() : null,
        ]);
    }
}
