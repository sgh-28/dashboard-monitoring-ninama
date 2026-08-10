<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectTask;
use App\Models\Role;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleAccessAndLoginTest extends TestCase
{
    use RefreshDatabase;

    private array $roles = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin', 'direktur', 'customer', 'pegawai', 'marketing'] as $role) {
            $this->roles[$role] = Role::create(['name' => $role]);
        }
    }

    public function test_login_redirects_users_by_role(): void
    {
        $cases = [
            'admin' => 'main.dashboard',
            'direktur' => 'direktur.dashboard',
            'pegawai' => 'employee.tasks.index',
            'customer' => 'customer.dashboard',
            'marketing' => 'marketing.index',
        ];

        foreach ($cases as $role => $routeName) {
            $user = User::factory()->create([
                'role_id' => $this->roles[$role]->id,
                'email' => "{$role}@example.test",
                'password' => Hash::make('password'),
            ]);

            $this->post(route('login.post'), [
                'email' => $user->email,
                'password' => 'password',
            ])->assertRedirect(route($routeName));

            $this->post(route('logout'))->assertRedirect('/login');
        }
    }

    public function test_only_admin_can_access_google_disconnect_and_logout_does_not_disconnect_google(): void
    {
        $director = $this->user('direktur');
        $admin = $this->user('admin');

        $this->actingAs($director)->post(route('auth.google.disconnect'))->assertForbidden();

        $this->mock(GoogleCalendarService::class, function ($mock) {
            $mock->shouldReceive('disconnect')->once();
        });

        $this->actingAs($admin)->post(route('auth.google.disconnect'))
            ->assertRedirect(route('main.dashboard'));
    }

    public function test_director_has_read_access_but_cannot_open_admin_create_project_page(): void
    {
        $director = $this->user('direktur');

        $this->actingAs($director)->get(route('direktur.dashboard'))->assertOk();
        $this->actingAs($director)->get(route('admin.projects.create'))->assertForbidden();
    }

    public function test_debug_integration_route_is_not_accessible_to_non_admin_roles(): void
    {
        $director = $this->user('direktur');

        $this->actingAs($director)->get(route('test.integration'))->assertForbidden();
    }

    public function test_customer_can_only_open_own_project_detail(): void
    {
        $customer = $this->user('customer');
        $otherCustomer = $this->user('customer');
        $ownProject = $this->project(customer: $customer);
        $otherProject = $this->project(customer: $otherCustomer, name: 'Other Project');

        $this->actingAs($customer)->get(route('customer.projects.show', $ownProject))->assertOk();
        $this->actingAs($customer)->get(route('customer.projects.show', $otherProject))->assertNotFound();
    }

    public function test_employee_can_only_open_assigned_task(): void
    {
        $employee = $this->user('pegawai', ['bidang' => 'web', 'jabatan' => 'UI/UX']);
        $otherEmployee = $this->user('pegawai', ['bidang' => 'web', 'jabatan' => 'Frontend']);
        $project = $this->project();
        $division = $this->division($project, 'UI/UX');

        $assignedTask = $this->task($project, $division, $employee);
        $otherTask = $this->task($project, $division, $otherEmployee, 'Other task');

        $this->actingAs($employee)->get(route('employee.tasks.show', $assignedTask))->assertOk();
        $this->actingAs($employee)->get(route('employee.tasks.show', $otherTask))->assertForbidden();
    }

    public function test_only_authorized_project_management_can_complete_project_after_all_tasks_approved(): void
    {
        $normalEmployee = $this->user('pegawai', ['bidang' => 'web', 'jabatan' => 'UI/UX']);
        $wrongBidangPm = $this->user('pegawai', ['bidang' => 'internet', 'jabatan' => 'Project Management']);
        $pm = $this->user('pegawai', ['bidang' => 'web', 'jabatan' => 'Project Management']);

        $project = $this->project();
        $this->division($project, 'Project Management');
        $division = $this->division($project, 'UI/UX');
        $task = $this->task($project, $division, $normalEmployee);

        $this->actingAs($normalEmployee)->post(route('employee.tasks.projects.complete', $project))->assertForbidden();
        $this->actingAs($wrongBidangPm)->post(route('employee.tasks.projects.complete', $project))->assertForbidden();

        $this->actingAs($pm)->post(route('employee.tasks.projects.complete', $project))
            ->assertSessionHas('error');

        $task->update([
            'status' => 'done',
            'progress' => 100,
            'completed_at' => now(),
            'actual_end_date' => now()->toDateString(),
            'verification_status' => 'approved',
            'verified_by' => $pm->id,
            'verified_at' => now(),
        ]);

        $this->actingAs($pm)->post(route('employee.tasks.projects.complete', $project))
            ->assertRedirect(route('employee.tasks.index'));

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'done',
            'completed_by' => $pm->id,
        ]);
    }

    public function test_admin_update_cannot_mark_project_as_done(): void
    {
        $admin = $this->user('admin');
        $customer = $this->user('customer');
        $project = $this->project(customer: $customer);
        $this->division($project, 'UI/UX');

        $this->actingAs($admin)->put(route('admin.projects.update', $project), [
            'name' => $project->name,
            'category' => 'web',
            'status' => 'done',
            'customer_id' => $customer->id,
            'start_date' => '2026-07-01',
            'deadline' => '2026-07-31',
            'sla' => 100,
            'divisions' => ['UI/UX'],
        ])->assertRedirect(route('admin.projects.index'));

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'ongoing',
        ]);
    }

    private function user(string $role, array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => $this->roles[$role]->id,
            'password' => Hash::make('password'),
        ], $overrides));
    }

    private function project(?User $customer = null, string $name = 'Website Test'): Project
    {
        $customer ??= $this->user('customer');

        return Project::create([
            'name' => $name,
            'category' => 'web',
            'status' => 'ongoing',
            'client_name' => $customer->name,
            'customer_id' => $customer->id,
            'start_date' => '2026-07-01',
            'deadline' => '2026-07-31',
            'sla' => 100,
        ]);
    }

    private function division(Project $project, string $name): ProjectDivision
    {
        return ProjectDivision::create([
            'project_id' => $project->id,
            'name' => $name,
            'progress' => 0,
        ]);
    }

    private function task(Project $project, ProjectDivision $division, User $employee, string $title = 'Task Test'): ProjectTask
    {
        return ProjectTask::create([
            'project_id' => $project->id,
            'division_id' => $division->id,
            'assigned_to' => $employee->id,
            'title' => $title,
            'planned_start_date' => '2026-07-01',
            'planned_end_date' => '2026-07-05',
            'deadline' => '2026-07-05',
            'status' => 'pending',
            'verification_status' => 'pending',
        ]);
    }
}
