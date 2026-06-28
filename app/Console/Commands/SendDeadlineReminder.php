<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Mail;
use App\Mail\DeadlineMail;

class SendDeadlineReminder extends Command
{
    protected $signature = 'ninama:send-reminders';
    protected $description = 'Send reminders for upcoming project deadlines';

    public function handle()
    {
        $today = now();
        $projects = Project::whereBetween('deadline', [
            $today->toDateString(),
            $today->copy()->addDays(3)->toDateString()
        ])->get();

        foreach ($projects as $p) {
            // WhatsApp
            $wa = new WhatsAppService();
            $wa->sendMessage(
                config('services.callmebot.phone_to'),
                "Reminder: Proyek {$p->name} mendekati deadline pada {$p->deadline}"
            );

            // Email
            Mail::to(config('mail.from.address'))
                ->send(new DeadlineMail($p->name, $p->deadline));
        }

        $this->info('Reminders sent for '.$projects->count().' projects.');
        
        return Command::SUCCESS;
    }
}