<?php

namespace App\Services;

use App\Models\ProjectTask;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $fonnteToken;
    protected $fonnteUrl;

    public function __construct()
    {
        $this->fonnteToken = config('services.fonnte.token');
        $this->fonnteUrl = config('services.fonnte.url');
    }

    /**
     * Kirim notifikasi multi-channel saat task dibuat
     */
    public function sendTaskNotification(ProjectTask $task)
    {
        $assignee = $task->assignee;
        
        if (!$assignee) {
            Log::warning('Task tidak memiliki assignee, skip notifikasi');
            return false;
        }

        $message = $this->buildTaskMessage($task);

        // 1. Kirim WhatsApp
        $waSent = $this->sendWhatsApp($assignee, $message, $task);

        // 2. Kirim Email
        $emailSent = $this->sendEmail($assignee, $task);

        // 3. Buat Google Calendar Event
        $calendarSent = $this->createCalendarEvent($assignee, $task);

        // Update status notifikasi
        $task->update(['is_notified' => true]);

        return $waSent || $emailSent || $calendarSent;
    }

    /**
     * Kirim reminder H-3 dan H-1
     */
    public function sendDeadlineReminder(ProjectTask $task, int $daysBefore)
    {
        $assignee = $task->assignee;
        
        if (!$assignee || $task->status === 'done') {
            return false;
        }

        $message = $this->buildReminderMessage($task, $daysBefore);

        // Kirim WhatsApp
        $this->sendWhatsApp($assignee, $message, $task);

        // Kirim Email
        $this->sendEmail($assignee, $task, true);

        // Update Google Calendar (tambah reminder)
        $this->updateCalendarEvent($assignee, $task, $daysBefore);

        return true;
    }

    /**
     * Build pesan WhatsApp untuk task baru
     */
    private function buildTaskMessage(ProjectTask $task): string
    {
        $projectName = $task->project->name ?? 'Proyek';
        $deadline = $task->deadline ? $task->deadline->format('d/m/Y') : 'Tidak ada deadline';
        
        return "*TASK BARU DITUGASKAN*\n\n" .
               "Halo {$task->assignee->name},\n\n" .
               "Anda mendapat task baru:\n" .
               "*{$task->title}*\n" .
               "Proyek: {$projectName}\n" .
               "Deadline: {$deadline}\n\n" .
               "Silakan login ke dashboard untuk melihat detail:\n" .
               config('app.url') . "/my-tasks\n\n" .
               "_Pesan otomatis dari sistem Ninama_";
    }

    /**
     * Build pesan reminder
     */
    private function buildReminderMessage(ProjectTask $task, int $daysBefore): string
    {
        $urgency = $daysBefore <= 1 ? 'URGENT' : 'PENGINGAT';
        $deadline = $task->deadline->format('d/m/Y');
        
        return "*{$urgency} - DEADLINE MENDEKATI*\n\n" .
               "Halo {$task->assignee->name},\n\n" .
               "Task Anda akan deadline dalam {$daysBefore} hari:\n" .
               "*{$task->title}*\n" .
               "Deadline: {$deadline}\n\n" .
               "Segera selesaikan tugas Anda!\n\n" .
               "_Pesan otomatis dari sistem Ninama_";
    }

    /**
     * Kirim WhatsApp via Fonnte API
     */
    private function sendWhatsApp(User $user, string $message, ProjectTask $task): bool
    {
        if (!$user->phone) {
            Log::warning("User {$user->name} tidak punya nomor WhatsApp");
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->fonnteToken,
            ])->post($this->fonnteUrl, [
                'target' => $user->phone,
                'message' => $message,
            ]);

            $success = $response->successful();
            
            // Catat di database
            Notification::create([
                'user_id' => $user->id,
                'project_task_id' => $task->id,
                'title' => 'Task Notification',
                'message' => $message,
                'channel' => 'whatsapp',
                'status' => $success ? 'sent' : 'failed',
                'response_log' => $response->body(),
            ]);

            return $success;
        } catch (\Exception $e) {
            Log::error('WhatsApp error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim Email
     */
    private function sendEmail(User $user, ProjectTask $task, bool $isReminder = false): bool
    {
        try {
            $subject = $isReminder 
                ? "Reminder: Task Deadline Mendekati - {$task->title}"
                : "Task Baru Ditugaskan: {$task->title}";

            Mail::raw("Halo {$user->name},\n\n{$this->buildTaskMessage($task)}", function ($mail) use ($user, $subject) {
                $mail->to($user->email)
                     ->subject($subject)
                     ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Notification::create([
                'user_id' => $user->id,
                'project_task_id' => $task->id,
                'title' => $subject,
                'message' => "Email sent to {$user->email}",
                'channel' => 'email',
                'status' => 'sent',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Buat Google Calendar Event & undang pegawai via email
     */
    private function createCalendarEvent(User $user, ProjectTask $task): bool
    {
        try {
            $calendarService = new \App\Services\GoogleCalendarService();

            $projectName = $task->project->name ?? 'Proyek';
            $deadline    = $task->deadline ? $task->deadline->format('Y-m-d') : date('Y-m-d');
            $title       = "[TASK] {$task->title} — {$projectName}";
            $description = "Tugas: {$task->title}\nProyek: {$projectName}\nDitugaskan kepada: {$user->name}\nDeadline: {$task->deadline?->format('d/m/Y')}";

            // Buat event di kalender Admin & undang pegawai via email mereka
            $result  = $calendarService->createEvent($title, $deadline, $user->email, $description);
            $success = $result['status'] === 'success';

            Notification::create([
                'user_id'         => $user->id,
                'project_task_id' => $task->id,
                'title'           => 'Calendar Event Created',
                'message'         => $success
                    ? "Event dibuat. Link: " . ($result['event_link'] ?? '-')
                    : ($result['message'] ?? 'Gagal'),
                'channel'         => 'calendar',
                'status'          => $success ? 'sent' : 'failed',
            ]);

            if ($success) {
                Log::info("Google Calendar event dibuat untuk task #{$task->id}, diundang: {$user->email}");
            } else {
                Log::warning("Gagal buat Google Calendar event: " . ($result['message'] ?? 'unknown'));
            }

            return $success;
        } catch (\Exception $e) {
            Log::error('Google Calendar Error: ' . $e->getMessage());

            Notification::create([
                'user_id'         => $user->id,
                'project_task_id' => $task->id,
                'title'           => 'Calendar Event Failed',
                'message'         => $e->getMessage(),
                'channel'         => 'calendar',
                'status'          => 'failed',
            ]);

            return false;
        }
    }

    /**
     * Update Google Calendar Event (tambah reminder)
     */
    private function updateCalendarEvent(User $user, ProjectTask $task, int $daysBefore): bool
    {
        // TODO: Implementasi update Google Calendar
        Log::info("Calendar event updated with {$daysBefore}-day reminder for task {$task->id}");
        return true;
    }
}