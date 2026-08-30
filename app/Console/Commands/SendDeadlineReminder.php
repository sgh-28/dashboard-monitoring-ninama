<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;

class SendDeadlineReminder extends Command
{
    protected $signature = 'ninama:send-reminders';
    protected $description = 'Send H-3 and H-1 reminders for unfinished task deadlines';

    public function handle(NotificationService $notificationService): int
    {
        $today = Carbon::now(config('app.timezone', 'Asia/Jakarta'))->startOfDay();
        $processed = 0;
        $directorProcessed = 0;
        $directors = User::whereHas('role', fn($query) => $query->where('name', 'direktur'))
            ->whereNotNull('phone')
            ->get();

        foreach ([3, 1] as $daysBefore) {
            $deadline = $today->copy()->addDays($daysBefore)->toDateString();

            $tasks = ProjectTask::with(['assignee', 'project'])
                ->whereDate('deadline', $deadline)
                ->whereNotIn('status', ['done', 'completed'])
                ->whereNotNull('assigned_to')
                ->get();

            foreach ($tasks as $task) {
                $notificationService->sendDeadlineReminder($task, $daysBefore);
                $processed++;
            }

            $projects = Project::with('customer')
                ->whereDate('deadline', $deadline)
                ->whereNotIn('status', ['done', 'completed', 'rejected'])
                ->get();

            foreach ($projects as $project) {
                foreach ($directors as $director) {
                    $notificationService->sendProjectDeadlineReminder($project, $director, $daysBefore);
                    $directorProcessed++;
                }
            }
        }

        $this->info("Deadline reminder processed for {$processed} task(s).");
        $this->info("Director project reminder processed for {$directorProcessed} project/director target(s).");
        
        return Command::SUCCESS;
    }
}
