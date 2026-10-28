<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use App\Services\ApplicationService;
use App\Services\EventPublicationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class VolunteerVerificationFlowTest extends DatabaseTestCase
{
    use A2Fixture;

    public function test_unverified_existing_account_logs_in_to_verification_then_returns_to_safe_intended_page(): void
    {
        $user = User::factory()->unverified()->create();
        $this->withSession(['url.intended' => '/volunteer/profile'])
            ->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('verification.notice'))
            ->assertSessionHas('url.intended', '/volunteer/profile');
        $this->get('/verify-email')->assertOk()->assertSeeText('Kirim ulang tautan verifikasi');
        $this->get('/volunteer/profile')->assertOk()->assertSeeText('Buka verifikasi email');
        $this->get($this->verificationUrl($user))->assertRedirect('/volunteer/profile');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_verification_rejects_external_and_cross_role_intended_urls(): void
    {
        foreach (['https://evil.example/volunteer/profile', '/admin/dashboard', '//evil.example'] as $intended) {
            $user = User::factory()->unverified()->create();
            $this->actingAs($user)->withSession(['url.intended' => $intended])
                ->get($this->verificationUrl($user))->assertRedirect('/volunteer/aktivitas');
            $this->assertFalse(session()->has('url.intended'));
        }
    }

    public function test_resend_sends_notification_and_verified_account_does_not_resend(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->post('/email/verification-notification')
            ->assertRedirect('/verify-email')->assertSessionHas('status', 'verification-link-sent');
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
        $user->markEmailAsVerified();
        $this->post('/email/verification-notification')->assertRedirect('/volunteer/aktivitas');
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    public function test_failed_resend_can_be_retried_without_verifying_account(): void
    {
        $user = User::factory()->unverified()->create();
        Notification::shouldReceive('send')->once()->andThrow(new TransportException('Mail unavailable'));
        $this->actingAs($user)->post('/email/verification-notification')
            ->assertRedirect('/verify-email')->assertSessionHas('status', 'verification-send-failed');
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        Notification::fake();
        $this->post('/email/verification-notification')->assertSessionHas('status', 'verification-link-sent');
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_resend_is_rate_limited(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);
        for ($i = 0; $i < 6; $i++) {
            $this->post('/email/verification-notification')->assertRedirect('/verify-email');
        }
        $this->post('/email/verification-notification')->assertStatus(429);
        Notification::assertSentToTimes($user, VerifyEmail::class, 6);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_guest_inactive_and_wrong_owner_cannot_verify_an_account(): void
    {
        $user = User::factory()->unverified()->create();
        $url = $this->verificationUrl($user);
        $this->get($url)->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $user->update(['is_active' => false]);
        $this->actingAs($user)->get($url)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->assertGuest();
    }

    public function test_expired_signature_does_not_verify_account(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user)->get($this->verificationUrl($user, true))->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_organizer_and_admin_keep_their_existing_login_destinations(): void
    {
        Notification::fake();
        $organizer = User::factory()->unverified()->create(['role' => 'organizer']);
        $this->post('/login', ['email' => $organizer->email, 'password' => 'password'])
            ->assertRedirect('/organizer/profile');
        $this->get('/verify-email')->assertRedirect('/organizer/profile');
        $this->post('/email/verification-notification')->assertRedirect('/organizer/profile');
        $this->assertFalse($organizer->fresh()->hasVerifiedEmail());
        Notification::assertNothingSent();
        $this->post('/logout');
        $admin = User::factory()->unverified()->create(['role' => 'admin']);
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect('/admin/dashboard');
    }

    public function test_applying_redirects_to_verification_then_creates_one_draft_after_signed_link(): void
    {
        $this->assessmentReady();
        [$owner, $event, $position] = $this->fixture();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);
        $user = User::factory()->unverified()->create();
        $user->volunteerProfile()->create([
            'phone' => '081234567890', 'birth_date' => '2000-01-01', 'gender' => 'male',
            'address' => 'Alamat uji', 'bio' => 'Profil uji', 'city_id' => $event->city_id,
        ]);
        $user->volunteerSkills()->create(['skill_id' => $position->positionSkills->first()->skill_id, 'level' => 'beginner']);
        $user->availabilitySlots()->create(['starts_at' => now()->addDay(), 'ends_at' => now()->addDays(2)]);

        $this->actingAs($user)->get("/events/{$event->id}")->assertOk()
            ->assertSeeText('Verifikasi email untuk melamar')->assertDontSeeText('Pilih posisi dan mulai lamaran');
        $this->post("/volunteer/positions/{$position->id}/applications")->assertRedirect('/verify-email');
        $this->assertDatabaseMissing('applications', ['volunteer_id' => $user->id]);
        try {
            app(ApplicationService::class)->storeDraft($user, $position);
            $this->fail('Service must also enforce verification.');
        } catch (AuthorizationException $e) {
            $this->assertStringContainsString('Verifikasi email', $e->getMessage());
        }

        // The verification CTA returns to this public event, without replaying a POST.
        $this->get("/events/{$event->id}/join")->assertRedirect('/verify-email');
        $this->get($this->verificationUrl($user))->assertRedirect("/events/{$event->id}");
        $this->assertDatabaseMissing('applications', ['volunteer_id' => $user->id]);
        $this->post("/volunteer/positions/{$position->id}/applications")->assertRedirect();
        $draft = Application::where('volunteer_id', $user->id)->sole();
        $this->assertSame('draft', $draft->status);
        $this->post("/volunteer/positions/{$position->id}/applications")
            ->assertRedirect(route('volunteer.applications.show', $draft));
        $this->assertSame(1, Application::where('volunteer_id', $user->id)->count());

        $user->forceFill(['email_verified_at' => null])->save();
        $document = $draft->documents()->create([
            'uploader_id' => $user->id, 'document_type' => 'cv', 'disk' => 'private',
            'storage_key' => 'verification-test/missing.pdf', 'original_name' => 'test.pdf',
            'mime_type' => 'application/pdf', 'size_bytes' => 1, 'checksum_sha256' => str_repeat('a', 64),
            'status' => 'missing',
        ]);
        $this->post("/volunteer/applications/{$draft->id}/submit")->assertRedirect('/verify-email');
        $this->post("/volunteer/applications/{$draft->id}/documents")->assertRedirect('/verify-email');
        $this->delete("/volunteer/documents/{$document->id}")->assertRedirect('/verify-email');
        $this->assertSame('missing', $document->fresh()->status);
        $this->assertSame('draft', $draft->fresh()->status);
        $this->assertNull($draft->fresh()->submitted_at);

        $other = User::factory()->create();
        $this->actingAs($other)->post("/volunteer/applications/{$draft->id}/submit")->assertForbidden();
        $this->actingAs($owner)->post("/volunteer/positions/{$position->id}/applications")->assertForbidden();
        $user->update(['is_active' => false]);
        $this->actingAs($user)->post("/volunteer/positions/{$position->id}/applications")->assertForbidden();
    }

    private function verificationUrl(User $user, bool $expired = false): string
    {
        return URL::temporarySignedRoute('verification.verify', $expired ? now()->subMinute() : now()->addHour(), [
            'id' => $user->id, 'hash' => sha1($user->email),
        ]);
    }
}
