<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectTask;
use App\Models\Role;
use App\Models\User;
use App\Services\MilestoneService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDateValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $customer;
    private User $employee;
    private Project $project;
    private ProjectDivision $division;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'admin']);
        $customerRole = Role::create(['name' => 'customer']);
        $pegawaiRole = Role::create(['name' => 'pegawai']);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id]);
        $this->customer = User::factory()->create(['role_id' => $customerRole->id]);
        $this->employee = User::factory()->create([
            'role_id' => $pegawaiRole->id,
            'bidang' => 'web',
            'jabatan' => 'UI/UX',
        ]);

        $this->project = Project::create([
            'name' => 'Website Test',
            'category' => 'web',
            'status' => 'ongoing',
            'client_name' => $this->customer->name,
            'customer_id' => $this->customer->id,
            'start_date' => '2026-07-01',
            'deadline' => '2026-07-31',
            'sla' => 100,
        ]);

        $this->division = ProjectDivision::create([
            'project_id' => $this->project->id,
            'name' => 'UI/UX',
            'progress' => 0,
        ]);

        $this->mock(NotificationService::class, function ($mock) {
            $mock->shouldReceive('sendTaskNotification')->andReturn(true);
        });

        $this->mock(MilestoneService::class, function ($mock) {
            $mock->shouldReceive('generateMilestonesFromTasks')->andReturn(true);
            $mock->shouldReceive('syncProjectMilestoneStatuses')->andReturn(true);
        });
    }

    public function test_admin_can_create_task_on_project_period_boundaries(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.tasks.store'), [
            'project_id' => $this->project->id,
            'division_id' => $this->division->id,
            'tasks' => [
                [
                    'title' => 'Boundary task',
                    'planned_start_date' => '2026-07-01',
                    'deadline' => '2026-07-31',
                    'description' => 'Valid boundary dates',
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.tasks.index.by.project', $this->project->id));
        $this->assertDatabaseHas('project_tasks', [
            'project_id' => $this->project->id,
            'division_id' => $this->division->id,
            'assigned_to' => $this->employee->id,
            'title' => 'Boundary task',
        ]);
    }

    public function test_admin_cannot_create_task_outside_project_period(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('admin.tasks.create', ['project_id' => $this->project->id]))
            ->post(route('admin.tasks.store'), [
                'project_id' => $this->project->id,
                'division_id' => $this->division->id,
                'tasks' => [
                    [
                        'title' => 'Outside task',
                        'planned_start_date' => '2026-06-30',
                        'deadline' => '2026-07-05',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.tasks.create', ['project_id' => $this->project->id]));
        $response->assertSessionHasErrors('tasks.0.deadline');
        $this->assertDatabaseMissing('project_tasks', ['title' => 'Outside task']);
    }

    public function test_admin_cannot_update_task_deadline_outside_project_period(): void
    {
        $task = ProjectTask::create([
            'project_id' => $this->project->id,
            'division_id' => $this->division->id,
            'assigned_to' => $this->employee->id,
            'title' => 'Editable task',
            'planned_start_date' => '2026-07-05',
            'planned_end_date' => '2026-07-10',
            'deadline' => '2026-07-10',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.tasks.edit', $task))
            ->put(route('admin.tasks.update', $task), [
                'project_id' => $this->project->id,
                'division_id' => $this->division->id,
                'title' => 'Editable task',
                'description' => null,
                'assigned_to' => $this->employee->id,
                'planned_start_date' => '2026-07-05',
                'deadline' => '2026-08-01',
                'status' => 'pending',
            ]);

        $response->assertRedirect(route('admin.tasks.edit', $task));
        $response->assertSessionHasErrors('deadline');
        $this->assertSame('2026-07-10', $task->fresh()->deadline->toDateString());
    }

    public function test_admin_cannot_update_project_period_that_excludes_existing_task(): void
    {
        ProjectTask::create([
            'project_id' => $this->project->id,
            'division_id' => $this->division->id,
            'assigned_to' => $this->employee->id,
            'title' => 'Existing task',
            'planned_start_date' => '2026-07-10',
            'planned_end_date' => '2026-07-20',
            'deadline' => '2026-07-20',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.projects.edit', $this->project))
            ->put(route('admin.projects.update', $this->project), [
                'name' => $this->project->name,
                'category' => 'web',
                'customer_id' => $this->customer->id,
                'start_date' => '2026-07-01',
                'deadline' => '2026-07-15',
                'sla' => 100,
                'divisions' => ['UI/UX'],
            ]);

        $response->assertRedirect(route('admin.projects.edit', $this->project));
        $response->assertSessionHasErrors('deadline');
        $this->assertSame('2026-07-31', $this->project->fresh()->deadline->toDateString());
    }
}
