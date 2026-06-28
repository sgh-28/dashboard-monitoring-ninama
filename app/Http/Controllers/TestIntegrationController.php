<?php

namespace App\Http\Controllers;

use App\Services\GoogleCalendarService;
use App\Services\WhatsAppService;
use App\Mail\DeadlineMail;
use Illuminate\Support\Facades\Mail;
use Exception;

class TestIntegrationController extends Controller
{
    public function testAll()
    {
        $project  = "Redesign UI Ninama";
        $deadline = "2025-06-20";
        $phone    = config('services.fonnte.phone_to');

        $results = [
            'calendar' => ['status' => 'Pending', 'message' => ''],
            'whatsapp' => ['status' => 'Pending', 'message' => ''],
            'email'    => ['status' => 'Pending', 'message' => '']
        ];

        /** GOOGLE CALENDAR */
        try {
            $calendar = new GoogleCalendarService();
            $event = $calendar->createEvent("Deadline Proyek: $project", $deadline);

            $results['calendar'] = [
                'status' => 'Success',
                'message' => $event['event_link'] ?? 'Event berhasil dibuat'
            ];
        } catch (Exception $e) {
            $results['calendar'] = ['status' => 'Failed', 'message' => $e->getMessage()];
        }

        /** WHATSAPP */
        try {
            $wa = new WhatsAppService();
            $message = "📌 *Reminder Project*\n\n".
                       "Nama: *$project*\n".
                       "Deadline: *$deadline*\n".
                       "Status: Pending";

            $send = $wa->sendMessage($phone, $message);

            $results['whatsapp'] = [
                'status' => 'Success',
                'message' => 'Pesan WhatsApp berhasil dikirim'
            ];
        } catch (Exception $e) {
            $results['whatsapp'] = ['status' => 'Failed', 'message' => $e->getMessage()];
        }

        /** EMAIL */
        try {
            Mail::to(config('mail.from.address'))->send(new DeadlineMail($project, $deadline));

            $results['email'] = [
                'status' => 'Success',
                'message' => 'Email berhasil dikirim'
            ];
        } catch (Exception $e) {
            $results['email'] = ['status' => 'Failed', 'message' => $e->getMessage()];
        }

        return view('integration-result', compact('results'));
    }
}
