<?php

namespace App\Services;

use App\Mail\VolunteerApplicationMail;
use App\Models\Application;
use App\Models\NotificationOutbox;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class VolunteerEmailDelivery
{
    public function deliver(int $id): string
    {
        $row = DB::transaction(function () use ($id) {
            $row = NotificationOutbox::whereKey($id)->lockForUpdate()->first();
            if (! $row || $row->channel !== 'mail' || ! in_array($row->status, ['pending', 'failed'], true)
                || $row->available_at->isFuture() || $row->attempts >= 5) {
                return null;
            }
            $row->forceFill(['status' => 'processing', 'attempts' => $row->attempts + 1,
                'claim_token' => (string) Str::uuid(), 'claimed_at' => now(), 'error_code' => null])->save();

            return $row;
        }, 3);
        if (! $row) {
            return 'ignored';
        }

        try {
            // Recheck at delivery time; queued messages never authorize assessment access.
            $application = Application::find($row->application_id);
            if (! $application || ! app(VolunteerEmailService::class)->eligible($application, $row->type, $row)) {
                $this->update($row, ['status' => 'skipped', 'error_code' => 'no_longer_eligible']);

                return 'skipped';
            }
            $mail = new VolunteerApplicationMail($application, $row);
            $mail->content(); // Validate configuration and metadata before invoking the transport.
        } catch (\Throwable) {
            $this->update($row, ['status' => 'failed', 'error_code' => 'mail_preparation_failed',
                'available_at' => now()->addMinutes(min(60, 2 ** $row->attempts))]);

            return 'failed';
        }
        try {
            // SMTP is outside the claim transaction and all domain transactions.
            Mail::to($application->volunteer->email)->send($mail);
        } catch (\Throwable) {
            // SMTP may have accepted the email before an acknowledgement/event handler failed.
            $this->update($row, ['status' => 'uncertain', 'error_code' => 'smtp_outcome_uncertain']);

            return 'uncertain';
        }
        // If recording SMTP success fails, leave the claim for review rather than duplicate a send.
        $this->update($row, ['status' => 'sent', 'delivered_at' => now(), 'error_code' => null]);

        return 'sent';
    }

    private function update(NotificationOutbox $row, array $values): void
    {
        NotificationOutbox::whereKey($row->id)->where('status', 'processing')->where('claim_token', $row->claim_token)
            ->update([...$values, 'claim_token' => null]);
    }
}
