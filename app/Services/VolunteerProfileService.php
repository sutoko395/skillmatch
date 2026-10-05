<?php

namespace App\Services;

use App\Models\City;
use App\Models\Skill;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class VolunteerProfileService
{
    public function save(User $actor, array $data): void
    {
        Gate::forUser($actor)->authorize('updateProfile', $actor);
        abort_unless($actor->role === 'volunteer', 403);
        DB::transaction(function () use ($actor, $data) {
            $user = User::whereKey($actor->id)->lockForUpdate()->firstOrFail();
            abort_unless($user->is_active && $user->role === 'volunteer', 403);
            $city = City::whereKey($data['city_id'])->sharedLock()->first();
            $ids = array_column($data['skills'], 'skill_id');
            $skills = Skill::whereIn('id', $ids)->orderBy('id')->sharedLock()->get();
            if (! $city?->is_active || count(array_unique($ids)) !== count($ids) || $skills->where('is_active', true)->count() !== count($ids)) {
                throw ValidationException::withMessages(['skills' => 'Kota atau skill tidak lagi tersedia. Muat ulang formulir.']);
            }
            $slots = [];
            foreach ($data['availability_slots'] as $slot) {
                $start = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $slot['starts_at'], 'Asia/Jakarta')->utc();
                $end = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $slot['ends_at'], 'Asia/Jakarta')->utc();
                if ($end <= $start) {
                    throw ValidationException::withMessages(['availability_slots' => 'Interval harus berdurasi positif.']);
                }
                $slots[$start->toDateTimeString().'|'.$end->toDateTimeString()] = ['starts_at' => $start, 'ends_at' => $end];
            }
            $user->update(['name' => $data['name']]);
            $profile = $user->volunteerProfile()->updateOrCreate(['user_id' => $user->id], Arr::only($data, ['phone', 'birth_date', 'gender', 'address', 'bio', 'city_id']));
            $user->volunteerSkills()->delete();
            $user->volunteerSkills()->createMany($data['skills']);
            $user->availabilitySlots()->delete();
            $user->availabilitySlots()->createMany(array_values($slots));
            app(AuditService::class)->record($user, 'volunteer.profile.updated', $profile, ['after' => ['city_id' => $city->id, 'changed_fields' => array_keys($data)]]);
        });
    }
}
