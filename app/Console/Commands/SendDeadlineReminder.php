<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProjectTask;
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
        }

        $this->info("Deadline reminder processed for {$processed} task(s).");
        
        return Command::SUCCESS;
    }
}
