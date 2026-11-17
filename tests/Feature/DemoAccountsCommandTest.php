<?php

namespace Tests\Feature;

use App\Models\OrganizerProfile;
use App\Models\User;
use App\Models\VolunteerProfile;
use App\Services\LoginDestination;
use App\Services\ProfileEligibilityService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\DatabaseTestCase;

class DemoAccountsCommandTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'demo.enabled' => true,
            'demo.organizer_email' => 'eo-test@demo.test',
            'demo.volunteer_email' => 'vol-test@demo.test',
            'demo.password' => 'DemoTest2026!',
        ]);
    }

    public function test_command_creates_verified_accounts_with_complete_profiles(): void
    {
        $this->artisan('demo:accounts --force')->assertSuccessful();

        $organizer = User::where('email', 'eo-test@demo.test')->sole();
        $this->assertSame('organizer', $organizer->role);
        $this->assertTrue($organizer->is_active);
        $this->assertSame('active', $organizer->organizer_status);
        $this->assertTrue($organizer->hasVerifiedEmail());
        $this->assertInstanceOf(OrganizerProfile::class, $organizer->organizerProfile);
        $this->assertNotNull($organizer->organizerProfile->city_id);
        $this->assertSame('/organizer/aktivitas', app(LoginDestination::class)->defaultFor($organizer));

        $volunteer = User::where('email', 'vol-test@demo.test')->sole();
        $this->assertSame('volunteer', $volunteer->role);
        $this->assertTrue($volunteer->is_active);
        $this->assertTrue($volunteer->hasVerifiedEmail());
        $this->assertInstanceOf(VolunteerProfile::class, $volunteer->volunteerProfile);
        $this->assertTrue(app(ProfileEligibilityService::class)->check($volunteer)['complete']);
        $this->assertSame('/volunteer/aktivitas', app(LoginDestination::class)->defaultFor($volunteer));

        $this->assertTrue(Auth::validate(['email' => 'eo-test@demo.test', 'password' => 'DemoTest2026!']));
        $this->assertTrue(Auth::validate(['email' => 'vol-test@demo.test', 'password' => 'DemoTest2026!']));
    }

    public function test_command_is_idempotent_and_does_not_reset_existing_password(): void
    {
        $this->artisan('demo:accounts --force')->assertSuccessful();
        $volunteer = User::where('email', 'vol-test@demo.test')->sole();
        $sessionCount = $volunteer->availabilitySlots()->count();
        $skillCount = $volunteer->volunteerSkills()->count();

        $this->artisan('demo:accounts --force')->assertSuccessful();

        $this->assertSame(1, User::where('email', 'eo-test@demo.test')->count());
        $this->assertSame(1, User::where('email', 'vol-test@demo.test')->count());
        $this->assertSame($sessionCount, $volunteer->fresh()->availabilitySlots()->count());
        $this->assertSame($skillCount, $volunteer->fresh()->volunteerSkills()->count());
        $this->assertTrue(Hash::check('DemoTest2026!', $volunteer->fresh()->password));
    }

    public function test_reset_password_option_applies_a_new_demo_password(): void
    {
        $this->artisan('demo:accounts --force')->assertSuccessful();

        config(['demo.password' => 'NewDemo2026!']);
        $this->artisan('demo:accounts --force')->assertSuccessful();
        $this->assertTrue(Hash::check('DemoTest2026!', User::where('email', 'vol-test@demo.test')->sole()->password));

        $this->artisan('demo:accounts --force --reset-password')->assertSuccessful();
        $this->assertTrue(Hash::check('NewDemo2026!', User::where('email', 'vol-test@demo.test')->sole()->password));
        $this->assertTrue(Hash::check('NewDemo2026!', User::where('email', 'eo-test@demo.test')->sole()->password));
    }

    public function test_command_requires_enable_flag_and_password(): void
    {
        config(['demo.enabled' => false]);
        $this->artisan('demo:accounts --force')->assertFailed();
        $this->assertFalse(User::where('email', 'eo-test@demo.test')->exists());

        config(['demo.enabled' => true, 'demo.password' => '']);
        $this->artisan('demo:accounts --force')->assertFailed();
        $this->assertFalse(User::where('email', 'eo-test@demo.test')->exists());
    }
}
