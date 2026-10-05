<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\DatabaseTestCase;

class FoundationAccessTest extends DatabaseTestCase
{
    public static function destinations(): array
    {
        return [
            ['admin', 'active', true, '/admin/dashboard'],
            ['volunteer', 'pending', true, '/volunteer/aktivitas'],
            ['organizer', 'active', true, '/organizer/aktivitas'],
            ['organizer', 'pending', true, '/organizer/profile/pending'],
            ['organizer', 'inactive', true, '/organizer/profile/pending'],
            ['organizer', 'pending', false, '/verify-email'],
            ['volunteer', 'pending', false, '/verify-email'],
        ];
    }

    #[DataProvider('destinations')]
    public function test_shared_login_destinations(string $role, string $status, bool $verified, string $destination): void
    {
        $user = User::factory()->create(['role' => $role, 'organizer_status' => $status, 'email_verified_at' => $verified ? now() : null]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password', 'role' => 'admin'])->assertRedirect($destination);
        $this->assertAuthenticatedAs($user);
        $page = $this->get($destination)->assertOk();
        $this->get('/')->assertOk()
            ->assertSeeText('Hubungkan Talent Relawan dengan Event Terbaik')
            ->assertSee($role === 'admin' ? 'Buka Panel Admin' : 'Buka Aktivitas Saya');
        if ($role === 'admin') {
            $page->assertViewIs('admin.dashboard')
                ->assertViewHas('totalVolunteers', User::where('role', 'volunteer')->count())
                ->assertViewHas('totalOrganizers', User::where('role', 'organizer')->count())
                ->assertViewHas('totalSkills', Skill::count())
                ->assertViewHas('totalCategories', Category::count())
                ->assertViewHas('pendingEvents', Event::where('status', 'pending')->count())
                ->assertSee('Aksi Cepat Admin')
                ->assertSee('analitik lengkap belum tersedia');
        }
    }

    public static function intended(): array
    {
        return [
            ['/volunteer/profile', '/volunteer/profile'],
            ['/admin/dashboard', '/volunteer/aktivitas'],
            ['https://evil.example/volunteer/profile', '/volunteer/aktivitas'],
            ['//evil.example/volunteer/profile', '/volunteer/aktivitas'],
            ['/\\evil.example', '/volunteer/aktivitas'],
            ['/%2f%2fevil.example', '/volunteer/aktivitas'],
            ['/volunteer/../admin/dashboard', '/volunteer/aktivitas'],
            ['/unknown', '/volunteer/aktivitas'],
        ];
    }

    #[DataProvider('intended')]
    public function test_intended_url_is_local_and_authorized(string $url, string $expected): void
    {
        $user = User::factory()->create();
        $this->withSession(['url.intended' => $url])->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect($expected);
        $this->assertFalse(session()->has('url.intended'));
    }

    public function test_same_origin_intended_is_allowed_and_wrong_port_is_rejected(): void
    {
        $user = User::factory()->create();
        config(['app.url' => 'http://skillmatch.test']);
        $this->withSession(['url.intended' => 'http://skillmatch.test/volunteer/profile'])->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/volunteer/profile');
        $this->post('/logout');
        $this->withSession(['url.intended' => 'http://skillmatch.test:9999/volunteer/profile'])->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/volunteer/aktivitas');
    }

    public function test_direct_cross_role_and_pending_access(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $volunteer = User::factory()->unverified()->create();
        $this->actingAs($volunteer)->get('/admin/dashboard')->assertForbidden();
        $this->get('/volunteer/profile')->assertRedirect('/verify-email');
        $organizer = User::factory()->unverified()->create(['role' => 'organizer', 'organizer_status' => 'inactive']);
        $this->actingAs($organizer)->get('/organizer/profile')->assertOk();
        $this->get('/organizer/profile/pending')->assertOk();
        $this->get('/volunteer/profile')->assertForbidden();
        $organizer->markEmailAsVerified();
        $this->get('/organizer/aktivitas')->assertRedirect('/organizer/profile/pending');
    }

    public function test_inactive_login_and_existing_session_are_rejected(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $user->update(['is_active' => true]);
        $this->actingAs($user)->get('/volunteer/aktivitas')->assertOk();
        User::whereKey($user->id)->update(['is_active' => false]);
        $this->get('/volunteer/aktivitas')->assertForbidden();
        $this->assertGuest();
        $this->get('/volunteer/aktivitas')->assertRedirect('/login');
    }

    public function test_registration_rejects_admin_and_ignores_sensitive_status(): void
    {
        Notification::fake();
        $data = ['name' => 'Demo', 'email' => 'registration@example.test', 'password' => 'password', 'password_confirmation' => 'password', 'role' => 'admin'];
        $this->post('/register', $data)->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => $data['email']]);
        $data['role'] = 'organizer';
        $this->post('/register', $data + ['is_active' => false, 'organizer_status' => 'active'])->assertRedirect('/verify-email');
        $user = User::where('email', $data['email'])->firstOrFail();
        $this->assertTrue($user->is_active);
        $this->assertSame('pending', $user->organizer_status);
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_logout_and_profile_policy(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('updateProfile', $other));
        $this->actingAs($user)->withSession(['marker' => 'private'])->post('/logout')->assertRedirect('/login')->assertSessionMissing('marker');
        $this->assertGuest();
        $this->get('/logout')->assertStatus(405);
        $this->get('/admin/login')->assertNotFound();
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
        }
        $response->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logout_requires_csrf_outside_the_test_bypass(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->app['env'] = 'local';
        $this->post('/logout')->assertStatus(419);
        $this->assertAuthenticatedAs($user);
        $this->withSession(['_token' => 'csrf-test-only'])->post('/logout', ['_token' => 'csrf-test-only'])->assertRedirect('/login');
        $this->assertGuest();
    }
}
