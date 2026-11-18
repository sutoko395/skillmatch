<?php

namespace App\Console\Commands;

use App\Models\AvailabilitySlot;
use App\Models\City;
use App\Models\OrganizerProfile;
use App\Models\Skill;
use App\Models\User;
use App\Models\VolunteerProfile;
use App\Models\VolunteerSkill;
use App\Services\ProfileEligibilityService;
use Database\Seeders\EastJavaRegionSeeder;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DemoAccounts extends Command
{
    protected $signature = 'demo:accounts
        {--force : Jalankan tanpa konfirmasi interaktif}
        {--reset-password : Set ulang password akun demo yang sudah ada}';

    protected $description = 'Buat akun demo organizer dan volunteer yang sudah terverifikasi.';

    public function handle(): int
    {
        if (! config('demo.enabled')) {
            $this->error('Akun demo dinonaktifkan. Set DEMO_ACCOUNTS_ENABLED=true pada environment demo/staging.');

            return self::FAILURE;
        }

        $password = (string) config('demo.password');
        if ($password === '') {
            $this->error('Set DEMO_ACCOUNT_PASSWORD terlebih dahulu. Password demo tidak disimpan di repository.');

            return self::FAILURE;
        }

        $organizerEmail = strtolower(trim((string) config('demo.organizer_email')));
        $volunteerEmail = strtolower(trim((string) config('demo.volunteer_email')));

        try {
            Validator::make(
                ['organizer' => $organizerEmail, 'volunteer' => $volunteerEmail],
                [
                    'organizer' => ['required', 'email', 'max:255'],
                    'volunteer' => ['required', 'email', 'max:255', 'different:organizer'],
                ]
            )->validate();
        } catch (ValidationException $exception) {
            $this->error('Email demo tidak valid: '.collect($exception->errors())->flatten()->first());

            return self::FAILURE;
        }

        if (! $this->option('force')) {
            if (! $this->input->isInteractive()) {
                $this->error('Gunakan --force untuk deployment non-interaktif.');

                return self::FAILURE;
            }

            if (! $this->confirm('Buat atau pastikan akun demo '.$organizerEmail.' dan '.$volunteerEmail.'?')) {
                return self::FAILURE;
            }
        }

        $resetPassword = (bool) $this->option('reset-password');

        try {
            [$organizer, $volunteer] = DB::transaction(function () use ($organizerEmail, $volunteerEmail, $password, $resetPassword) {
                if (! Skill::where('is_active', true)->exists()) {
                    app(MasterDataSeeder::class)->run();
                }
                if (! City::where('is_active', true)->exists()) {
                    app(EastJavaRegionSeeder::class)->run();
                }

                $city = City::where('is_active', true)
                    ->orderByRaw("CASE WHEN name = 'Kota Malang' THEN 0 ELSE 1 END")
                    ->orderBy('name')
                    ->firstOrFail();

                $organizer = $this->ensureUser($organizerEmail, 'Demo EO', 'organizer', $password, $resetPassword);
                $organizer->forceFill(['is_active' => true, 'organizer_status' => 'active'])->save();
                if (! $organizer->hasVerifiedEmail()) {
                    $organizer->markEmailAsVerified();
                }
                $organizerProfile = OrganizerProfile::firstOrCreate(['user_id' => $organizer->id], [
                    'organization_name' => 'EO Demo SkillMatch',
                    'contact_person' => 'Demo EO',
                    'phone' => '081234567890',
                    'email' => $organizerEmail,
                    'address' => 'Alamat demo',
                    'city' => $city->name,
                    'city_id' => $city->id,
                    'description' => 'Organisasi demo untuk mencoba alur SkillMatch.',
                ]);
                if (! $organizerProfile->city_id) {
                    $organizerProfile->forceFill(['city_id' => $city->id, 'city' => $city->name])->save();
                }

                $volunteer = $this->ensureUser($volunteerEmail, 'Demo Volunteer', 'volunteer', $password, $resetPassword);
                $volunteer->forceFill(['is_active' => true, 'organizer_status' => 'pending'])->save();
                if (! $volunteer->hasVerifiedEmail()) {
                    $volunteer->markEmailAsVerified();
                }

                VolunteerProfile::firstOrCreate(['user_id' => $volunteer->id], [
                    'phone' => '081234567890',
                    'birth_date' => '2000-01-01',
                    'gender' => 'female',
                    'city' => $city->name,
                    'city_id' => $city->id,
                    'address' => 'Alamat demo',
                    'bio' => 'Volunteer demo untuk mencoba alur SkillMatch.',
                ]);

                if (! $volunteer->volunteerSkills()->exists()) {
                    $levels = ['intermediate', 'advanced', 'beginner', 'expert'];
                    foreach (Skill::where('is_active', true)->orderBy('id')->take(4)->get()->values() as $index => $skill) {
                        VolunteerSkill::firstOrCreate(
                            ['user_id' => $volunteer->id, 'skill_id' => $skill->id],
                            ['level' => $levels[$index] ?? 'beginner']
                        );
                    }
                }

                if (! $volunteer->availabilitySlots()->exists()) {
                    $start = now()->addDays(7)->setTimezone('Asia/Jakarta')->startOfDay()->addHours(9);
                    AvailabilitySlot::create([
                        'user_id' => $volunteer->id,
                        'starts_at' => $start->utc(),
                        'ends_at' => $start->copy()->addHours(3)->utc(),
                    ]);
                }

                $eligibility = app(ProfileEligibilityService::class)->check($volunteer);
                if (! $eligibility['complete']) {
                    throw new \RuntimeException('Profil volunteer demo belum lengkap: '.implode(', ', $eligibility['missing_fields']));
                }

                return [$organizer->fresh(), $volunteer->fresh()];
            }, 3);
        } catch (\Throwable $exception) {
            $this->error('Gagal membuat akun demo: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Akun demo siap.');
        $this->line('Organizer: '.$organizer->email.' (aktif, terverifikasi)');
        $this->line('Volunteer: '.$volunteer->email.' (terverifikasi, profil lengkap)');
        $this->line('Password: mengikuti DEMO_ACCOUNT_PASSWORD.');

        return self::SUCCESS;
    }

    private function ensureUser(string $email, string $name, string $role, string $password, bool $resetPassword): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password, 'role' => $role, 'is_active' => true]
        );

        if ($user->role !== $role) {
            throw new \RuntimeException('Email '.$email.' sudah dipakai role lain ('.$user->role.').');
        }

        if ($resetPassword) {
            $user->forceFill(['password' => $password])->save();
        }

        return $user;
    }
}
