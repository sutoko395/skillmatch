<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\DatabaseTestCase;

class RegistrationTest extends DatabaseTestCase
{
    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Notification::fake();
        $response = $this->post('/register', [
            'name' => 'Test User',
            'role' => 'volunteer',
            'email_verified_at' => now(),
            'is_active' => false,
            'organizer_status' => 'active',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice'));
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertTrue($user->is_active);
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertSame('pending', $user->organizer_status);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_organizer_registration_retains_profile_onboarding_without_email_gate(): void
    {
        Notification::fake();
        $this->post('/register', [
            'name' => 'Organizer', 'email' => 'organizer-flow@example.test',
            'role' => 'organizer', 'password' => 'password', 'password_confirmation' => 'password',
            'is_active' => false, 'organizer_status' => 'active', 'email_verified_at' => now(),
        ])->assertRedirect(route('organizer.profile.edit'));
        $user = User::where('email', 'organizer-flow@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->is_active);
        $this->assertSame('pending', $user->organizer_status);
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertNothingSent();
    }

    public function test_mail_failure_keeps_new_volunteer_account_and_offers_retry(): void
    {
        Notification::shouldReceive('send')->once()->andThrow(new TransportException('Mail unavailable'));
        $this->post('/register', [
            'name' => 'Volunteer', 'email' => 'mail-failure@example.test',
            'role' => 'volunteer', 'password' => 'password', 'password_confirmation' => 'password',
        ])->assertRedirect(route('verification.notice'))->assertSessionHas('status', 'verification-send-failed');
        $user = User::where('email', 'mail-failure@example.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->hasVerifiedEmail());
    }
}
