<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Skill;
use App\Models\User;
use App\Services\ProfileEligibilityService;
use App\Services\VolunteerProfileService;
use Illuminate\Validation\ValidationException;
use Tests\DatabaseTestCase;

class PhoneValidationTest extends DatabaseTestCase
{
    private function volunteerData(): array
    {
        return [
            'name' => 'Phone validation fixture', 'phone' => '081234567890',
            'birth_date' => '2000-01-01', 'gender' => 'female', 'address' => 'Alamat uji', 'bio' => 'Bio uji',
            'city_id' => City::create(['name' => 'Phone city '.uniqid(), 'is_active' => true])->id,
            'skills' => [['skill_id' => Skill::create(['name' => 'Phone skill '.uniqid(), 'is_active' => true])->id, 'level' => 'advanced']],
            'availability_slots' => [['starts_at' => '2026-11-10T09:00', 'ends_at' => '2026-11-10T12:00']],
        ];
    }

    private function organizerData(): array
    {
        return ['organization_name' => 'Phone organization', 'contact_person' => 'PIC Uji',
            'phone' => '081234567890', 'address' => 'Alamat uji', 'city' => 'Malang', 'description' => 'Organisasi uji'];
    }

    public function test_invalid_phone_requests_never_create_or_overwrite_volunteer_profiles(): void
    {
        $user = User::factory()->create(['role' => 'volunteer']);
        $data = $this->volunteerData();
        $this->actingAs($user);
        foreach (['abcdefghi', '08123abc456', '1234567', '1234567890123456', '0812 3456 7890', '0812-3456-7890', '1e12345678', '１２３４５６７８', '++62812345678', ['08123456789']] as $phone) {
            $this->postJson(route('volunteer.profile.update'), array_replace($data, ['phone' => $phone]))->assertUnprocessable()->assertJsonValidationErrors('phone');
            $this->assertSame(0, $user->volunteerProfile()->count());
            $this->assertSame(0, $user->volunteerSkills()->count());
        }
        foreach (['081234567890', '+6281234567890'] as $phone) {
            $this->post(route('volunteer.profile.update'), array_replace($data, ['phone' => $phone]))->assertSessionHasNoErrors()->assertRedirect();
            $this->assertSame($phone, $user->volunteerProfile()->first()->phone);
        }
        $this->postJson(route('volunteer.profile.update'), array_replace($data, ['phone' => 'letters', 'name' => 'Invalid overwrite']))->assertJsonValidationErrors('phone');
        $this->assertSame('+6281234567890', $user->volunteerProfile()->first()->phone);
        $this->assertSame($data['name'], $user->fresh()->name);
    }

    public function test_organizer_create_and_edit_use_same_rule_without_changing_approval(): void
    {
        $user = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $data = $this->organizerData();
        $this->actingAs($user)->postJson(route('organizer.profile.update'), array_replace($data, ['phone' => 'letters']))->assertJsonValidationErrors('phone');
        $this->assertSame(0, $user->organizerProfile()->count());
        $user->organizerProfile()->create($data);
        foreach (['letters', '081234abc890', '123', '1234567890123456'] as $phone) {
            $this->postJson(route('organizer.profile.update'), array_replace($data, ['phone' => $phone]))->assertUnprocessable()->assertJsonValidationErrors('phone');
            $this->assertSame($data['phone'], $user->organizerProfile()->first()->phone);
            $this->assertSame('active', $user->fresh()->organizer_status);
        }
        $this->post(route('organizer.profile.update'), array_replace($data, ['phone' => '+6281234567890']))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('+6281234567890', $user->organizerProfile()->first()->phone);
        $this->assertSame('active', $user->fresh()->organizer_status);
    }

    public function test_direct_service_call_rejects_invalid_phone_and_legacy_invalid_phone_is_not_complete(): void
    {
        $user = User::factory()->create(['role' => 'volunteer']);
        $data = $this->volunteerData();
        try {
            app(VolunteerProfileService::class)->save($user, array_replace($data, ['phone' => 'letters']));
            $this->fail('Direct service writes must validate phone.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('phone', $exception->errors());
        }
        $this->assertSame(0, $user->volunteerProfile()->count());
        app(VolunteerProfileService::class)->save($user, $data);
        $this->assertTrue(app(ProfileEligibilityService::class)->check($user->fresh())['complete']);
        $user->volunteerProfile()->update(['phone' => 'old invalid phone']);
        $eligibility = app(ProfileEligibilityService::class)->check($user->fresh());
        $this->assertFalse($eligibility['complete']);
        $this->assertSame(['phone'], $eligibility['missing_fields']);
        $this->assertSame('old invalid phone', $user->volunteerProfile()->first()->phone);
        app(VolunteerProfileService::class)->save($user, $data);
        $this->assertTrue(app(ProfileEligibilityService::class)->check($user->fresh())['complete']);
    }

    public function test_profile_forms_expose_native_phone_validation_and_date_limit(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'volunteer']))->get(route('volunteer.profile.edit'))
            ->assertOk()->assertSee('type="tel"', false)->assertSee('pattern="\+?[0-9]{8,15}"', false)
            ->assertSee('autocomplete="tel"', false)->assertSee('max="'.now()->format('Y-m-d').'"', false);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $this->actingAs($organizer)->get(route('organizer.profile.edit'))->assertOk()->assertSee('type="tel"', false);
        $organizer->organizerProfile()->create($this->organizerData());
        $this->get(route('organizer.profile.edit'))->assertOk()->assertSee('pattern="\+?[0-9]{8,15}"', false);
    }

    public function test_numeric_package_bounds_reject_direct_invalid_requests_and_forms_match_limits(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $data = ['name' => 'Validation package '.uniqid(), 'price' => 0, 'max_positions' => 2,
            'max_applications' => 30, 'max_registration_days' => 7, 'is_active' => 1];
        foreach (['price' => 1000000001, 'max_positions' => 1001, 'max_applications' => 1000001, 'max_registration_days' => 366] as $field => $value) {
            $this->postJson(route('admin.packages.store'), array_replace($data, [$field => $value]))->assertUnprocessable()->assertJsonValidationErrors($field);
        }
        foreach (['abc', '-1', '1.5'] as $value) {
            $this->postJson(route('admin.packages.store'), array_replace($data, ['max_positions' => $value]))->assertJsonValidationErrors('max_positions');
        }
        foreach (['admin.packages.create', 'admin.packages.index'] as $route) {
            $response = $this->get(route($route))->assertOk();
            foreach ([1000000000, 1000, 1000000, 365] as $max) {
                $response->assertSee('max="'.$max.'"', false);
            }
        }
    }
}
