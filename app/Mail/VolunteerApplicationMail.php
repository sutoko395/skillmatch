<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\NotificationOutbox;
use App\Services\VolunteerEmailService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

class VolunteerApplicationMail extends Mailable
{
    public function __construct(public Application $application, public NotificationOutbox $outbox) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->outbox->type) {
            VolunteerEmailService::FAILED => 'Informasi lamaranmu',
            VolunteerEmailService::AVAILABLE => 'Assessment tersedia',
            VolunteerEmailService::REMINDER => 'Pengingat: assessment segera ditutup',
            default => ($this->outbox->data['decision'] ?? null) === 'accepted' ? 'Selamat, kamu diterima!' : 'Hasil seleksi lamaran',
        };

        return new Envelope(subject: $subject.' — '.$this->application->event->title);
    }

    public function headers(): Headers
    {
        return new Headers(messageId: hash('sha256', config('app.url').'|'.$this->outbox->dedupe_key).'@skillmatch.local');
    }

    public function content(): Content
    {
        $service = app(VolunteerEmailService::class);
        $assessment = in_array($this->outbox->type, [VolunteerEmailService::AVAILABLE, VolunteerEmailService::REMINDER], true);
        $base = rtrim((string) config('app.url'), '/');
        $origin = parse_url($base);
        if (! in_array($origin['scheme'] ?? null, ['http', 'https'], true) || empty($origin['host'])
            || isset($origin['user']) || isset($origin['pass']) || isset($origin['query']) || isset($origin['fragment'])) {
            throw new \RuntimeException('Invalid application URL configuration');
        }

        return new Content(view: 'mail.volunteer-application', with: [
            'recipientName' => $this->application->volunteer->name,
            'eventTitle' => $this->application->event->title,
            'positionName' => $this->application->position->name,
            'type' => $this->outbox->type, 'decision' => $this->outbox->data['decision'] ?? null,
            'deadline' => $assessment ? $service->deadline($this->application)?->setTimezone('Asia/Jakarta') : null,
            'assessmentInfo' => $assessment ? $service->assessment($this->application) : null,
            'targetUrl' => $base.route($assessment ? 'volunteer.assessments.show' : 'volunteer.applications.show', $this->application, false),
        ]);
    }
}
