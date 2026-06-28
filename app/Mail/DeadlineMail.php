<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeadlineMail extends Mailable
{
    use Queueable, SerializesModels;

    public $projectName;
    public $deadline;

    public function __construct($projectName, $deadline)
    {
        $this->projectName = $projectName;
        $this->deadline = $deadline;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat Deadline Proyek',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.deadline',
            with: [
                'project' => $this->projectName,
                'deadline' => $this->deadline,
            ],
        );
    }
}