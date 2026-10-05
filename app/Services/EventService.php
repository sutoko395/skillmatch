<?php

namespace App\Services;

use App\Models\City;
use App\Models\Event;
use App\Models\EventPosition;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EventService
{
    public function editable(Event $event): void
    {
        if ($event->published_at || ! in_array($event->status, ['draft', 'rejected']) || $event->lifecycle_status !== 'upcoming' || $event->orders()->exists() || $event->entitlement()->exists()) {
            throw ValidationException::withMessages(['event' => 'Konfigurasi terkunci. Hanya draft/revisi tanpa transaksi yang dapat diubah.']);
        }
    }

    public function save(User $actor, array $input, ?Event $event = null): Event
    {
        return DB::transaction(function () use ($actor, $input, $event) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            $event = $event ? Event::lockForUpdate()->findOrFail($event->id) : new Event;
            Gate::forUser($actor)->authorize($event->exists ? 'update' : 'create', $event->exists ? $event : Event::class);
            if ($event->exists) {
                $this->editable($event);
            }
            $data = Validator::make($input, [
                'title' => 'required|string|max:255', 'description' => 'required|string|max:10000', 'location' => 'required|string|max:255',
                'category_id' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
                'city_id' => ['required', Rule::exists('cities', 'id')->where('is_active', true)],
                'starts_at' => 'required|date_format:Y-m-d\TH:i', 'ends_at' => 'required|date_format:Y-m-d\TH:i|after:starts_at',
                'registration_opens_at' => 'required|date_format:Y-m-d\TH:i',
                'registration_deadline' => 'required|date_format:Y-m-d\TH:i|after:registration_opens_at|before_or_equal:starts_at',
            ])->validate();
            foreach (['starts_at', 'ends_at', 'registration_opens_at', 'registration_deadline'] as $field) {
                $data[$field] = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $data[$field], 'Asia/Jakarta')->utc();
            }
            if ($data['starts_at'] <= now()) {
                throw ValidationException::withMessages(['starts_at' => 'Awal event harus di masa depan.']);
            }
            foreach ($event->exists ? $event->positions()->with('schedules')->get() : [] as $p) {
                foreach ($p->schedules as $s) {
                    if ($s->starts_at < $data['starts_at'] || $s->ends_at > $data['ends_at'] || $data['registration_deadline'] > $s->starts_at) {
                        throw ValidationException::withMessages(['starts_at' => 'Jadwal posisi existing tidak sesuai rentang baru.']);
                    }
                }
            }
            $data += ['organizer_id' => $actor->id, 'status' => 'draft', 'city' => City::findOrFail($data['city_id'])->name];
            foreach (['start' => 'starts_at', 'end' => 'ends_at'] as $prefix => $field) {
                $local = $data[$field]->setTimezone('Asia/Jakarta');
                $data[$prefix.'_date'] = $local->format('Y-m-d');
                $data[$prefix.'_time'] = $local->format('H:i:s');
            }
            $event->forceFill($data)->save();
            $event->increment('revision');
            app(AuditService::class)->record($actor, 'event.saved', $event, ['after' => ['status' => $event->status, 'revision' => $event->revision]]);

            return $event->fresh();
        }, 3);
    }

    public function position(Event $event, User $actor, array $input, ?EventPosition $position = null): EventPosition
    {
        return DB::transaction(function () use ($event, $actor, $input, $position) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            $event = Event::lockForUpdate()->findOrFail($event->id);
            Gate::forUser($actor)->authorize('update', $event);
            $this->editable($event);
            if ($position) {
                $position = $event->positions()->lockForUpdate()->findOrFail($position->id);
            }
            $data = Validator::make($input, [
                'name' => 'required|string|max:255', 'description' => 'nullable|string|max:5000', 'quota' => 'required|integer|min:1|max:100000',
                'required_full_availability' => 'required|boolean', 'required_same_city' => 'required|boolean',
                'skills' => 'required|array|min:1|max:100', 'skills.*.skill_id' => ['required', 'distinct', Rule::exists('skills', 'id')->where('is_active', true)],
                'skills.*.minimum_level' => 'required|in:beginner,intermediate,advanced,expert', 'skills.*.is_required' => 'required|boolean',
                'schedules' => 'required|array|min:1|max:100', 'schedules.*.starts_at' => 'required|date_format:Y-m-d\TH:i',
                'schedules.*.ends_at' => 'required|date_format:Y-m-d\TH:i',
                'requirements' => 'sometimes|array|max:50', 'requirements.*.name' => 'required|string|max:255',
                'requirements.*.description' => 'nullable|string|max:2000', 'requirements.*.kind' => 'required|in:manual,document',
                'requirements.*.document_type' => 'nullable|in:cv,supporting', 'requirements.*.is_required' => 'required|boolean',
            ])->validate();
            if (! $position && $event->package_snapshot && $event->positions()->count() >= $event->package_snapshot['max_positions']) {
                throw ValidationException::withMessages(['quota' => 'Batas posisi paket tercapai.']);
            }
            $schedules = [];
            foreach ($data['schedules'] as $row) {
                $s = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $row['starts_at'], 'Asia/Jakarta')->utc();
                $e = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $row['ends_at'], 'Asia/Jakarta')->utc();
                if ($e <= $s || $s < $event->starts_at || $e > $event->ends_at || $s < $event->registration_deadline) {
                    throw ValidationException::withMessages(['schedules' => 'Jadwal harus berdurasi positif, dalam rentang event dan setelah deadline.']);
                }
                $schedules[$s->timestamp.':'.$e->timestamp] = ['starts_at' => $s, 'ends_at' => $e];
            }
            foreach ($data['requirements'] ?? [] as $r) {
                if ($r['kind'] === 'document' && empty($r['document_type'])) {
                    throw ValidationException::withMessages(['requirements' => 'Jenis dokumen wajib dipilih untuk syarat dokumen.']);
                }
            }
            $position ??= new EventPosition(['event_id' => $event->id]);
            $position->fill(collect($data)->only(['name', 'description', 'quota', 'required_full_availability', 'required_same_city'])->all())->save();
            $position->positionSkills()->delete();
            $position->positionSkills()->createMany($data['skills']);
            $position->schedules()->delete();
            $position->schedules()->createMany(array_values($schedules));
            $position->requirements()->delete();
            $position->requirements()->createMany($data['requirements'] ?? []);
            $event->forceFill(['status' => 'draft', 'revision' => $event->revision + 1])->save();
            app(AuditService::class)->record($actor, 'position.saved', $position);

            return $position;
        }, 3);
    }

    public function removePosition(Event $event, EventPosition $position, User $actor): void
    {
        DB::transaction(function () use ($event, $position, $actor) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            $event = Event::lockForUpdate()->findOrFail($event->id);
            Gate::forUser($actor)->authorize('update', $event);
            $this->editable($event);
            $position = $event->positions()->lockForUpdate()->findOrFail($position->id);
            app(AuditService::class)->record($actor, 'position.deleted', $position);
            $position->delete();
            $event->forceFill(['status' => 'draft', 'revision' => $event->revision + 1])->save();
        }, 3);
    }

    public function submit(Event $event, User $actor): void
    {
        DB::transaction(function () use ($event, $actor) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            $event = Event::lockForUpdate()->findOrFail($event->id);
            Gate::forUser($actor)->authorize('update', $event);
            if ($event->status === 'pending') {
                return;
            }
            $this->editable($event);
            app(EventConfiguration::class)->assertValid($event);
            if ($event->registration_deadline <= now()) {
                throw ValidationException::withMessages(['event' => 'Pendaftaran sudah ditutup.']);
            }
            $event->forceFill(['status' => 'pending', 'submitted_at' => now(), 'revision' => $event->revision + 1])->save();
            app(AuditService::class)->record($actor, 'event.submitted', $event, ['after' => ['status' => 'pending', 'revision' => $event->revision]]);
        }, 3);
    }
}
