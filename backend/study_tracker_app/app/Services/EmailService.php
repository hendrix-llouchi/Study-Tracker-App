<?php

namespace App\Services;

use App\Models\User;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Send email and log it.
     */
    public function sendEmail(User $user, string $emailType, string $mailableClass, array $data = []): void
    {
        try {
            Mail::to($user->email)->send(new $mailableClass($user, $data));

            EmailLog::create([
                'user_id' => $user->id,
                'email_type' => $emailType,
                'recipient_email' => $user->email,
                'subject' => $data['subject'] ?? 'Notification',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            EmailLog::create([
                'user_id' => $user->id,
                'email_type' => $emailType,
                'recipient_email' => $user->email,
                'subject' => $data['subject'] ?? 'Notification',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}

