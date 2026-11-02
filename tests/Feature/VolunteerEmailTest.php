<?php

namespace Tests\Feature;

use App\Mail\VolunteerApplicationMail;
use App\Models\Application;
use App\Models\NotificationOutbox;
use App\Models\User;
use App\Services\AssessmentService;
use App\Services\EntitlementService;
use App\Services\MatchingEvaluationService;
use App\Services\ScreeningService;
use App\Services\SelectionService;
use App\Services\VolunteerEmailDelivery;
use App\Services\VolunteerEmailService;
use Carbon\CarbonImmutable;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\DatabaseTestCase;
use Tests\Support\ParticipantAssessmentFixture;

class VolunteerEmailTest extends DatabaseTestCase
{
    use ParticipantAssessmentFixture;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'https://skillmatch.example']);
        Mail::fake();
    }

    private function enqueue(Application $application, string $type = VolunteerEmailService::AVAILABLE): NotificationOutbox
    {
        return app(VolunteerEmailService::class)->enqueue($application, $type);
    }

    public function test_available_email_is_queued_once_and_sending_is_not_inline_with_matching(): void
    {
        $application = $this->participantApplication();
        $application->screeningResult()->delete();
        $application->matchResult()->delete();
        $application->update(['status' => 'submitted']);
        app(ScreeningService::class)->evaluate($application);
        // DatabaseTransactions defers afterCommit; run the real matching consumer explicitly.
        app(MatchingEvaluationService::class)->evaluate($application->fresh());
        $row = NotificationOutbox::where('application_id', $application->id)->sole();
        $this->assertSame(VolunteerEmailService::AVAILABLE, $row->type);
        $this->assertSame('assessment_pending', $application->fresh()->status);
        $this->assertSame($row->id, $this->enqueue($application->fresh())->id);
        Mail::assertNothingSent();
        $delivery = app(VolunteerEmailDelivery::class);
        $this->assertSame('sent', $delivery->deliver($row->id));
        $this->assertSame('ignored', $delivery->deliver($row->id));
        Mail::assertSent(VolunteerApplicationMail::class, fn ($mail) => $mail->hasTo($application->volunteer->email));
        Mail::assertSentCount(1);
    }

    public function test_failed_screening_emits_only_failure_email_and_transaction_rollback_removes_outbox(): void
    {
        $application = $this->participantApplication();
        $application->screeningResult()->delete();
        $application->matchResult()->delete();
        $snapshot = $application->snapshot_json;
        $snapshot['profile_eligibility'] = ['complete' => false, 'missing_fields' => ['phone']];
        $application->update(['status' => 'submitted', 'snapshot_json' => $snapshot]);
        DB::beginTransaction();
        app(ScreeningService::class)->evaluate($application->fresh());
        $this->assertSame(VolunteerEmailService::FAILED, NotificationOutbox::where('application_id', $application->id)->sole()->type);
        DB::rollBack();
        $this->assertSame('submitted', $application->fresh()->status);
        $this->assertFalse(NotificationOutbox::where('application_id', $application->id)->exists());
        app(ScreeningService::class)->evaluate($application->fresh());
        app(ScreeningService::class)->evaluate($application->fresh());
        $this->assertSame(1, NotificationOutbox::where('application_id', $application->id)->count());
        $this->assertSame('screening_failed', $application->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_selection_uses_existing_transition_and_replay_does_not_add_email(): void
    {
        foreach (['accepted', 'rejected'] as $decision) {
            $application = $this->participantApplication();
            $application->update(['status' => 'under_review']);
            // The fixture's immutable schedule is synthetic, but valid for this test event.
            $application->event->forceFill(['registration_opens_at' => now()->subDay(), 'registration_deadline' => now()->addDay()])->save();
            app(EntitlementService::class)->activate($application->event);
            $actor = $application->event->organizer;
            $selection = app(SelectionService::class);
            $decision === 'accepted' ? $selection->accept($application, $actor) : $selection->reject($application, $actor, 'Belum sesuai kebutuhan posisi');
            $row = NotificationOutbox::where('application_id', $application->id)->sole();
            $this->assertSame(VolunteerEmailService::DECISION, $row->type);
            $this->assertSame($decision, $row->data['decision']);
            $decision === 'accepted' ? $selection->accept($application, $actor) : $selection->reject($application, $actor, 'Belum sesuai kebutuhan posisi');
            $this->assertSame(1, NotificationOutbox::where('application_id', $application->id)->count());
            app(VolunteerEmailDelivery::class)->deliver($row->id);
        }
        Mail::assertSentCount(2);
    }

    public function test_h_minus_one_reminder_runs_once_and_rechecks_unstarted_status_before_sending(): void
    {
        $application = $this->participantApplication();
        $row = $this->enqueue($application);
        $delivery = app(VolunteerEmailDelivery::class);
        $delivery->deliver($row->id);
        $deadline = CarbonImmutable::parse($application->snapshot_json['assessment_deadline']);
        $emails = app(VolunteerEmailService::class);
        $this->travelTo($deadline->subDay()->subSecond());
        $this->assertSame(0, $emails->reminders());
        $this->travelTo($deadline->subDay());
        $this->assertSame(1, $emails->reminders());
        $this->assertSame(0, $emails->reminders());
        $reminder = NotificationOutbox::where('application_id', $application->id)->where('type', VolunteerEmailService::REMINDER)->sole();
        app(AssessmentService::class)->start($application->fresh(), $application->volunteer);
        $this->assertSame('skipped', $delivery->deliver($reminder->id));
        Mail::assertSentCount(1);
        $this->travelBack();
    }

    public function test_h_minus_one_reminder_is_delivered_once_with_wib_deadline(): void
    {
        $application = $this->participantApplication();
        $available = $this->enqueue($application);
        app(VolunteerEmailDelivery::class)->deliver($available->id);
        $deadline = CarbonImmutable::parse($application->snapshot_json['assessment_deadline']);
        $this->travelTo($deadline->subDay());
        $this->artisan('a4:remind-assessments')->assertSuccessful();
        $this->artisan('a4:remind-assessments')->assertSuccessful();
        $reminder = NotificationOutbox::where('application_id', $application->id)->where('type', VolunteerEmailService::REMINDER)->sole();
        $this->assertSame('sent', app(VolunteerEmailDelivery::class)->deliver($reminder->id));
        $this->assertSame('ignored', app(VolunteerEmailDelivery::class)->deliver($reminder->id));
        $html = (new VolunteerApplicationMail($application, $reminder))->render();
        $this->assertStringContainsString($deadline->setTimezone('Asia/Jakarta')->format('H:i').' WIB', $html);
        $this->assertStringContainsString('belum mulai mengerjakan', $html);
        Mail::assertSentCount(2);
        $this->travelBack();
    }

    public function test_recent_availability_or_delayed_initial_email_does_not_get_immediate_reminder(): void
    {
        foreach ([false, true] as $initialDelayed) {
            $application = $this->participantApplication();
            $deadline = CarbonImmutable::parse($application->snapshot_json['assessment_deadline']);
            if (! $initialDelayed) {
                $this->travelTo($deadline->subHours(23));
            }
            $row = $this->enqueue($application);
            $this->travelTo($deadline->subHours(22));
            app(VolunteerEmailDelivery::class)->deliver($row->id);
            $this->assertSame(0, app(VolunteerEmailService::class)->reminders());
            $this->travelBack();
        }
    }

    public function test_stale_assessment_emails_are_suppressed_at_delivery(): void
    {
        foreach (['withdrawn', 'rejected', 'cancelled', 'started', 'deadline', 'inactive', 'unverified'] as $case) {
            $application = $this->participantApplication();
            $row = $this->enqueue($application);
            match ($case) {
                'withdrawn', 'rejected' => $application->update(['status' => $case]),
                'cancelled' => $application->event->update(['lifecycle_status' => 'cancelled']),
                'started' => app(AssessmentService::class)->start($application, $application->volunteer),
                'deadline' => $this->travelTo(CarbonImmutable::parse($application->snapshot_json['assessment_deadline'])),
                'inactive' => $application->volunteer->update(['is_active' => false]),
                'unverified' => $application->volunteer->forceFill(['email_verified_at' => null])->save(),
            };
            $this->assertSame('skipped', app(VolunteerEmailDelivery::class)->deliver($row->id), $case);
            $this->travelBack();
        }
        Mail::assertNothingSent();
    }

    public function test_preparation_failure_retries_without_changing_application_or_duplicating_mail(): void
    {
        $application = $this->participantApplication();
        $row = $this->enqueue($application);
        $before = $application->fresh()->getRawOriginal();
        config(['app.url' => 'ftp://invalid.example']);
        $delivery = app(VolunteerEmailDelivery::class);
        $this->assertSame('failed', $delivery->deliver($row->id));
        $this->assertSame('mail_preparation_failed', $row->fresh()->error_code);
        $this->assertSame($before, $application->fresh()->getRawOriginal());
        $this->assertSame('ignored', $delivery->deliver($row->id));
        config(['app.url' => 'https://skillmatch.example']);
        $this->travel(3)->minutes();
        $this->assertSame('sent', $delivery->deliver($row->id));
        $this->assertSame('ignored', $delivery->deliver($row->id));
        Mail::assertSentCount(1);
        $this->travelBack();
    }

    public function test_uncertain_smtp_and_abandoned_claims_are_not_blindly_resent(): void
    {
        $application = $this->participantApplication();
        $row = $this->enqueue($application);
        Mail::shouldReceive('to')->once()->andThrow(new TransportException('SMTP outcome unknown'));
        $delivery = app(VolunteerEmailDelivery::class);
        $this->assertSame('uncertain', $delivery->deliver($row->id));
        $this->assertSame('ignored', $delivery->deliver($row->id));
        $this->assertNull($row->fresh()->delivered_at);
        Mail::swap(new MailManager($this->app));
        Mail::fake();
        $this->artisan('a4:send-volunteer-emails', ['--retry-failed' => true])->assertSuccessful();
        Mail::assertNothingSent();
        $row->update(['status' => 'processing', 'claimed_at' => now()->subMinutes(16), 'claim_token' => '00000000-0000-0000-0000-000000000001']);
        $this->artisan('a4:send-volunteer-emails')->assertSuccessful();
        $this->assertSame('uncertain', $row->fresh()->status);
        Mail::assertNothingSent();
    }

    public function test_mail_content_uses_configured_urls_and_never_discloses_keys_or_matching_score(): void
    {
        $application = $this->participantApplication();
        $row = $this->enqueue($application);
        $mail = new VolunteerApplicationMail($application, $row);
        $html = $mail->render();
        $this->assertStringContainsString('https://skillmatch.example/volunteer/applications/'.$application->id.'/assessment', $html);
        $this->assertStringContainsString('2 soal', $html);
        $this->assertStringContainsString('30 menit', $html);
        $this->assertStringContainsString('Mulai sekarang', $html);
        foreach (['Soal pertama', 'Opsi B', 'is_correct', 'Match Score', '127.0.0.1'] as $private) {
            $this->assertStringNotContainsString($private, $html);
        }
        $this->assertSame($mail->headers()->messageId, (new VolunteerApplicationMail($application, $row))->headers()->messageId);
        $application->event->update(['title' => '<script>alert(1)</script>']);
        $escaped = (new VolunteerApplicationMail($application->fresh(), $row))->render();
        $this->assertStringNotContainsString('<script>', $escaped);
    }

    public function test_email_deep_link_survives_login_only_for_the_correct_owner_without_starting_timer(): void
    {
        $application = $this->participantApplication();
        $path = '/volunteer/applications/'.$application->id.'/assessment';
        $this->get($path)->assertRedirect(route('login'));
        $this->withSession(['url.intended' => 'https://skillmatch.example'.$path])
            ->post('/login', ['email' => $application->volunteer->email, 'password' => 'password'])->assertRedirect($path);
        $this->get($path)->assertOk()->assertSee('Mulai sekarang');
        $this->assertFalse($application->assessmentAttempt()->exists());
        $this->post('/logout');
        $other = User::factory()->create(['role' => 'volunteer']);
        $this->withSession(['url.intended' => 'https://skillmatch.example'.$path])
            ->post('/login', ['email' => $other->email, 'password' => 'password'])->assertRedirect('/volunteer/aktivitas');
        $this->get($path)->assertForbidden();
    }

    public function test_non_target_transitions_and_submission_do_not_queue_volunteer_emails(): void
    {
        $application = $this->participantApplication();
        app(SelectionService::class)->withdraw($application, $application->volunteer);
        $this->assertFalse(NotificationOutbox::where('application_id', $application->id)->exists());
        Mail::assertNothingSent();
    }
}
