<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;

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
        
        if (!$assignee || $task->status === 'done' || !$task->deadline) {
            return [
                'whatsapp' => 'skipped',
                'email' => 'skipped',
                'calendar' => 'skipped',
            ];
        }

        $message = $this->buildReminderMessage($task, $daysBefore);
        $results = [];

        foreach (['whatsapp', 'email', 'calendar'] as $channel) {
            if ($this->reminderAlreadyRecorded($task, $channel, $daysBefore)) {
                $results[$channel] = 'skipped';
                continue;
            }

            $results[$channel] = match ($channel) {
                'whatsapp' => $this->sendWhatsApp($assignee, $message, $task, [
                    'title' => "Reminder H-{$daysBefore} Task",
                    'notification_type' => 'deadline_reminder',
                    'reminder_days_before' => $daysBefore,
                    'reminder_date' => now(config('app.timezone', 'Asia/Jakarta'))->toDateString(),
                ]),
                'email' => $this->sendEmail($assignee, $task, true, $message, [
                    'notification_type' => 'deadline_reminder',
                    'reminder_days_before' => $daysBefore,
                    'reminder_date' => now(config('app.timezone', 'Asia/Jakarta'))->toDateString(),
                ]),
                'calendar' => $this->updateCalendarEvent($assignee, $task, $daysBefore),
            };
        }

        return $results;
    }

    public function sendProjectDeadlineReminder(Project $project, User $director, int $daysBefore): bool
    {
        if (!$director->phone || !$project->deadline || in_array($project->status, ['done', 'completed', 'rejected'], true)) {
            return false;
        }

        $meta = $this->notificationMeta([
            'notification_type' => 'project_deadline_reminder',
            'reminder_days_before' => $daysBefore,
            'reminder_date' => now(config('app.timezone', 'Asia/Jakarta'))->toDateString(),
        ]);

        $title = "Reminder H-{$daysBefore} Deadline Proyek";

        if ($this->projectReminderAlreadyRecorded($project, $director, 'whatsapp', $daysBefore, $title)) {
            return false;
        }

        $message = $this->buildDirectorProjectReminderMessage($project, $director, $daysBefore);

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->fonnteToken,
            ])->post($this->fonnteUrl, [
                'target' => $director->phone,
                'message' => $message,
            ]);

            $success = $response->successful();
            $this->recordProjectNotification($director, $title, $message, 'whatsapp', $success ? 'sent' : 'failed', $response->body(), $meta);

            return $success;
        } catch (Throwable $e) {
            Log::error('Director project deadline WhatsApp error: ' . $e->getMessage());
            $this->recordProjectNotification($director, $title, $message, 'whatsapp', 'failed', $e->getMessage(), $meta);

            return false;
        }
    }

    private function reminderAlreadyRecorded(ProjectTask $task, string $channel, int $daysBefore): bool
    {
        return Notification::query()
            ->where('project_task_id', $task->id)
            ->where('channel', $channel)
            ->where('notification_type', 'deadline_reminder')
            ->where('reminder_days_before', $daysBefore)
            ->whereDate('reminder_date', now(config('app.timezone', 'Asia/Jakarta'))->toDateString())
            ->exists();
    }

    private function projectReminderAlreadyRecorded(Project $project, User $director, string $channel, int $daysBefore, string $title): bool
    {
        return Notification::query()
            ->where('user_id', $director->id)
            ->whereNull('project_task_id')
            ->where('channel', $channel)
            ->where('title', $title)
            ->where('notification_type', 'project_deadline_reminder')
            ->where('reminder_days_before', $daysBefore)
            ->whereDate('reminder_date', now(config('app.timezone', 'Asia/Jakarta'))->toDateString())
            ->where('message', 'like', '%' . $project->name . '%')
            ->exists();
    }

    private function notificationMeta(array $meta = []): array
    {
        return array_merge([
            'notification_type' => 'task_notification',
            'reminder_days_before' => null,
            'reminder_date' => null,
        ], $meta);
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

    private function buildDirectorProjectReminderMessage(Project $project, User $director, int $daysBefore): string
    {
        $urgency = $daysBefore <= 1 ? 'URGENT' : 'PENGINGAT';
        $deadline = $project->deadline->format('d/m/Y');
        $category = $project->category ? ucfirst($project->category) : '-';
        $progress = number_format((float) ($project->progress ?? 0), 0, ',', '.');

        return "*{$urgency} - DEADLINE PROYEK H-{$daysBefore}*\n\n" .
               "Halo {$director->name},\n\n" .
               "Proyek berikut mendekati deadline:\n" .
               "*{$project->name}*\n" .
               "Customer: " . ($project->customer?->company ?? $project->client_name ?? '-') . "\n" .
               "Bidang: {$category}\n" .
               "Progress: {$progress}%\n" .
               "Deadline: {$deadline}\n\n" .
               "Silakan pantau proyek melalui dashboard direktur:\n" .
               config('app.url') . "/direktur/dashboard\n\n" .
               "_Pesan otomatis dari sistem Ninama_";
    }

    /**
     * Kirim WhatsApp via Fonnte API
     */
    private function sendWhatsApp(User $user, string $message, ProjectTask $task, array $meta = []): bool
    {
        $meta = $this->notificationMeta($meta);

        if (!$user->phone) {
            Log::warning("User {$user->name} tidak punya nomor WhatsApp");
            $this->recordNotification($user, $task, $meta['title'] ?? 'WhatsApp Notification Failed', $message, 'whatsapp', 'failed', 'Nomor WhatsApp kosong.', $meta);
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
            
            $this->recordNotification($user, $task, $meta['title'] ?? 'Task Notification', $message, 'whatsapp', $success ? 'sent' : 'failed', $response->body(), $meta);

            return $success;
        } catch (Throwable $e) {
            Log::error('WhatsApp error: ' . $e->getMessage());
            $this->recordNotification($user, $task, $meta['title'] ?? 'WhatsApp Notification Failed', $message, 'whatsapp', 'failed', $e->getMessage(), $meta);
            return false;
        }
    }

    /**
     * Kirim Email
     */
    private function sendEmail(User $user, ProjectTask $task, bool $isReminder = false, ?string $message = null, array $meta = []): bool
    {
        $meta = $this->notificationMeta($meta);

        try {
            $subject = $isReminder 
                ? "Reminder: Task Deadline Mendekati - {$task->title}"
                : "Task Baru Ditugaskan: {$task->title}";
            $body = $message ?? $this->buildTaskMessage($task);

            Mail::raw("Halo {$user->name},\n\n{$body}", function ($mail) use ($user, $subject) {
                $mail->to($user->email)
                     ->subject($subject)
                     ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->recordNotification($user, $task, $subject, "Email sent to {$user->email}", 'email', 'sent', null, $meta);

            return true;
        } catch (Throwable $e) {
            Log::error('Email error: ' . $e->getMessage());
            $this->recordNotification($user, $task, "Email Failed: {$task->title}", $message ?? $task->title, 'email', 'failed', $e->getMessage(), $meta);
            return false;
        }
    }

    /**
     * Buat Google Calendar Event & undang pegawai via email
     */
    private function createCalendarEvent(User $user, ProjectTask $task): bool
    {
        try {
            $calendarService = app(\App\Services\GoogleCalendarService::class);

            $projectName = $task->project->name ?? 'Proyek';
            $deadline    = $task->deadline ? $task->deadline->format('Y-m-d') : date('Y-m-d');
            $title       = "[TASK] {$task->title} - {$projectName}";
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
        $meta = $this->notificationMeta([
            'notification_type' => 'deadline_reminder',
            'reminder_days_before' => $daysBefore,
            'reminder_date' => now(config('app.timezone', 'Asia/Jakarta'))->toDateString(),
        ]);

        try {
            $calendarService = app(\App\Services\GoogleCalendarService::class);
            $projectName = $task->project->name ?? 'Proyek';
            $title = "[REMINDER H-{$daysBefore}] {$task->title} - {$projectName}";
            $date = now(config('app.timezone', 'Asia/Jakarta'))->toDateString();
            $description = $this->buildReminderMessage($task, $daysBefore);
            $result = $calendarService->createEvent($title, $date, $user->email, $description);
            $success = ($result['status'] ?? null) === 'success';

            $this->recordNotification(
                $user,
                $task,
                "Calendar Reminder H-{$daysBefore}",
                $success ? ('Event reminder dibuat. Link: ' . ($result['event_link'] ?? '-')) : ($result['message'] ?? 'Gagal'),
                'calendar',
                $success ? 'sent' : 'failed',
                json_encode($result),
                $meta
            );

            return $success;
        } catch (Throwable $e) {
            Log::error('Google Calendar reminder error: ' . $e->getMessage());
            $this->recordNotification($user, $task, "Calendar Reminder H-{$daysBefore} Failed", $task->title, 'calendar', 'failed', $e->getMessage(), $meta);

            return false;
        }
    }

    private function recordNotification(User $user, ProjectTask $task, string $title, string $message, string $channel, string $status, ?string $responseLog = null, array $meta = []): Notification
    {
        $meta = $this->notificationMeta($meta);

        return Notification::create([
            'user_id' => $user->id,
            'project_task_id' => $task->id,
            'title' => $title,
            'message' => $message,
            'channel' => $channel,
            'status' => $status,
            'notification_type' => $meta['notification_type'],
            'reminder_days_before' => $meta['reminder_days_before'],
            'reminder_date' => $meta['reminder_date'],
            'response_log' => $responseLog,
        ]);
    }

    private function recordProjectNotification(User $user, string $title, string $message, string $channel, string $status, ?string $responseLog = null, array $meta = []): Notification
    {
        $meta = $this->notificationMeta($meta);

        return Notification::create([
            'user_id' => $user->id,
            'project_task_id' => null,
            'title' => $title,
            'message' => $message,
            'channel' => $channel,
            'status' => $status,
            'notification_type' => $meta['notification_type'],
            'reminder_days_before' => $meta['reminder_days_before'],
            'reminder_date' => $meta['reminder_date'],
            'response_log' => $responseLog,
        ]);
    }
}
