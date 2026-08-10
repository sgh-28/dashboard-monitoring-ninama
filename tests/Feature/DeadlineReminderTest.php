<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectTask;
use App\Models\Role;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DeadlineReminderTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;
    private Project $project;
    private ProjectDivision $division;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-08-09 08:00:00');
        config([
            'services.fonnte.url' => 'https://fonnte.test/send',
            'services.fonnte.token' => 'fake-token',
        ]);

        $pegawaiRole = Role::create(['name' => 'pegawai']);
        $this->employee = User::factory()->create([
            'role_id' => $pegawaiRole->id,
            'phone' => '08123456789',
            'email' => 'pegawai@example.test',
            'bidang' => 'web',
            'jabatan' => 'UI/UX',
        ]);

        $this->project = Project::create([
            'name' => 'Website Reminder',
            'category' => 'web',
            'status' => 'ongoing',
            'start_date' => '2026-08-01',
            'deadline' => '2026-08-31',
            'sla' => 100,
        ]);

        $this->division = ProjectDivision::create([
            'project_id' => $this->project->id,
            'name' => 'UI/UX',
            'progress' => 0,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_reminder_command_sends_only_h_three_and_h_one_task_deadline_reminders(): void
    {
        Http::fake(['*' => Http::response(['status' => true], 200)]);
        Mail::fake();
        $this->mock(GoogleCalendarService::class, function ($mock) {
            $mock->shouldReceive('createEvent')->andReturn(['status' => 'success', 'event_link' => 'https://calendar.test/event']);
        });

        $h3 = $this->task('H3 task', '2026-08-12');
        $h1 = $this->task('H1 task', '2026-08-10');
        $h2 = $this->task('H2 task', '2026-08-11');
        $done = $this->task('Done task', '2026-08-12', 'done');

        $this->artisan('ninama:send-reminders')->assertSuccessful();

        foreach ([$h3->id => 3, $h1->id => 1] as $taskId => $daysBefore) {
            foreach (['whatsapp', 'email', 'calendar'] as $channel) {
                $this->assertReminderNotification($taskId, $channel, 'sent', $daysBefore, '2026-08-09');
            }
        }

        $this->assertSame(0, Notification::where('project_task_id', $h2->id)->count());
        $this->assertSame(0, Notification::where('project_task_id', $done->id)->count());
    }

    public function test_reminder_command_deduplicates_same_task_channel_day_and_continues_when_one_channel_fails(): void
    {
        Http::fake(['*' => Http::response('provider error', 500)]);
        Mail::fake();
        $this->mock(GoogleCalendarService::class, function ($mock) {
            $mock->shouldReceive('createEvent')->andReturn(['status' => 'success', 'event_link' => 'https://calendar.test/event']);
        });

        $task = $this->task('Retry safe task', '2026-08-12');

        $this->artisan('ninama:send-reminders')->assertSuccessful();
        $this->artisan('ninama:send-reminders')->assertSuccessful();

        $this->assertReminderNotification($task->id, 'whatsapp', 'failed', 3, '2026-08-09');
        $this->assertReminderNotification($task->id, 'email', 'sent', 3, '2026-08-09');
        $this->assertReminderNotification($task->id, 'calendar', 'sent', 3, '2026-08-09');

        $this->assertSame(1, Notification::where('project_task_id', $task->id)->where('channel', 'whatsapp')->count());
        $this->assertSame(1, Notification::where('project_task_id', $task->id)->where('channel', 'email')->count());
        $this->assertSame(1, Notification::where('project_task_id', $task->id)->where('channel', 'calendar')->count());
    }

    private function assertReminderNotification(
        int $taskId,
        string $channel,
        string $status,
        int $daysBefore,
        string $expectedReminderDate
    ): void {
        $notification = Notification::query()
            ->where('project_task_id', $taskId)
            ->where('channel', $channel)
            ->where('status', $status)
            ->where('notification_type', 'deadline_reminder')
            ->where('reminder_days_before', $daysBefore)
            ->first();

        $this->assertNotNull($notification, "Reminder {$channel} H-{$daysBefore} untuk task {$taskId} tidak ditemukan.");
        $this->assertSame($expectedReminderDate, $notification->reminder_date->toDateString());
    }

    private function task(string $title, string $deadline, string $status = 'pending'): ProjectTask
    {
        return ProjectTask::create([
            'project_id' => $this->project->id,
            'division_id' => $this->division->id,
            'assigned_to' => $this->employee->id,
            'title' => $title,
            'planned_start_date' => '2026-08-01',
            'planned_end_date' => $deadline,
            'deadline' => $deadline,
            'status' => $status,
            'progress' => $status === 'done' ? 100 : 0,
        ]);
    }
}
