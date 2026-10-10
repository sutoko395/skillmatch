<?php

namespace App\Services;

use App\Models\City;
use App\Models\Event;
use App\Models\EventPosition;
use App\Models\Package;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EventService
{
    public function correctText(Event $event, User $actor, array $input): void
    {
        DB::transaction(function () use ($event, $actor, $input) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            $event = Event::lockForUpdate()->findOrFail($event->id);

            Gate::forUser($actor)->authorize('update', $event);

            if (
                ! $event->published_at ||
                in_array($event->lifecycle_status, ['completed', 'cancelled'])
            ) {
                throw ValidationException::withMessages([
                    'event' => 'Koreksi teks hanya untuk event yang pernah dipublikasikan dan belum terminal.',
                ]);
            }

            if (
                array_diff(
                    array_keys($input),
                    ['title', 'description', '_token', '_method']
                )
            ) {
                throw ValidationException::withMessages([
                    'event' => 'Koreksi ini hanya menerima judul dan deskripsi, bukan aturan kegiatan.',
                ]);
            }

            $data = Validator::make($input, [
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:10000',
            ])->validate();

            $event->fill($data);

            if (! $event->isDirty()) {
                return;
            }

            $fields = array_keys($event->getDirty());

            $event->forceFill([
                'revision' => $event->revision + 1,
            ])->save();

            app(AuditService::class)->record(
                $actor,
                'event.text_corrected',
                $event,
                [
                    'after' => [
                        'changed_fields' => $fields,
                        'revision' => $event->revision,
                    ],
                ],
                'text_correction'
            );
        }, 3);
    }

    public function removeDraft(Event $event, User $actor): void
    {
        DB::transaction(function () use ($event, $actor) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            $event = Event::lockForUpdate()->findOrFail($event->id);

            Gate::forUser($actor)->authorize('update', $event);

            $this->editable($event);

            if (
                $event->status !== 'draft' ||
                $event->submitted_at ||
                $event->positions()->exists()
            ) {
                throw ValidationException::withMessages([
                    'event' => 'Hanya draft awal tanpa posisi dan tanpa histori pengajuan yang dapat dihapus.',
                ]);
            }

            app(AuditService::class)->record(
                $actor,
                'event.draft_deleted',
                $event
            );

            $event->delete();
        }, 3);
    }

    public function editable(Event $event): void
    {
        if (
            $event->published_at ||
            ! in_array($event->status, ['draft', 'rejected']) ||
            $event->lifecycle_status !== 'upcoming' ||
            $event->orders()->exists() ||
            $event->entitlement()->exists()
        ) {
            throw ValidationException::withMessages([
                'event' => 'Konfigurasi terkunci. Hanya draft/revisi tanpa transaksi yang dapat diubah.',
            ]);
        }
    }

    public function save(
        User $actor,
        array $input,
        ?Event $event = null
    ): Event {
        return DB::transaction(function () use ($actor, $input, $event) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);

            $event = $event
                ? Event::lockForUpdate()->findOrFail($event->id)
                : new Event;

            Gate::forUser($actor)->authorize(
                $event->exists ? 'update' : 'create',
                $event->exists ? $event : Event::class
            );

            if ($event->exists) {
                $this->editable($event);
            }

           $data = Validator::make($input, [
    'title' => ['required', 'string', 'max:255'],
    'package_id' => [$event->package_snapshot ? 'sometimes' : 'required', 'integer'],
    'description' => ['required', 'string', 'max:10000'],
    'location' => ['required', 'string', 'max:255'],
    'city' => ['required', 'string', 'max:100'],
    'city_id' => [
        'required',
        'integer',
        Rule::exists('cities', 'id')->where('is_active', true),
    ],
    'category_id' => [
        'required',
        Rule::exists('categories', 'id')
            ->where('is_active', true),
    ],
                'starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
                'ends_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:starts_at'],
                'registration_opens_at' => ['required', 'date_format:Y-m-d\TH:i'],
                'registration_deadline' => [
                    'required',
                    'date_format:Y-m-d\TH:i',
                    'after:registration_opens_at',
                    'before_or_equal:starts_at',
                ],
            ], [
                'package_id.required' => 'Pilih paket event terlebih dahulu.',
                'package_id.integer' => 'Pilih paket dari daftar yang tersedia.',
                'city_id.required' => 'Pilih kota kegiatan.',
                'city_id.integer' => 'Pilih kota dari daftar yang tersedia.',
                'city_id.exists' => 'Kota yang dipilih tidak tersedia atau sudah nonaktif.',
            ])->validate();

            $data['city'] = City::findOrFail($data['city_id'])->name;

            foreach ([
                'starts_at',
                'ends_at',
                'registration_opens_at',
                'registration_deadline',
            ] as $field) {
                $data[$field] = CarbonImmutable::createFromFormat(
                    'Y-m-d\TH:i',
                    $data[$field],
                    'Asia/Jakarta'
                )->utc();
            }

            if ($data['starts_at']->lte(now())) {
                throw ValidationException::withMessages([
                    'starts_at' => 'Awal event harus di masa depan.',
                ]);
            }

            $beforePackage = $event->package_snapshot;
            $packageId = (int) ($data['package_id'] ?? $beforePackage['id']);
            unset($data['package_id']);
            if ($beforePackage && (int) $beforePackage['id'] === $packageId) {
                $snapshot = $beforePackage;
            } else {
                $package = Package::where('is_active', true)->lockForUpdate()->find($packageId);
                if (! $package) {
                    throw ValidationException::withMessages(['package_id' => 'Paket tidak tersedia atau sudah nonaktif. Pilih paket aktif.']);
                }
                $snapshot = $package->snapshot();
            }
            $data['package_snapshot'] = $snapshot;

            $data['organizer_id'] = $actor->id;
            $data['status'] = 'draft';

            if (empty($data['city_id']) && ! empty($data['city'])) {
                $data['city_id'] = \App\Models\City::where('name', $data['city'])->where('is_active', true)->value('id');
            }

            if (! $event->exists) {
                $data['revision'] = 1;
                $data['publication_status'] = 'unpublished';
                $data['lifecycle_status'] = 'upcoming';
            } else {
                $data['revision'] = ((int) $event->revision) + 1;
            }

            $data['start_date'] = $data['starts_at']
                ->setTimezone('Asia/Jakarta')
                ->format('Y-m-d');

            $data['end_date'] = $data['ends_at']
                ->setTimezone('Asia/Jakarta')
                ->format('Y-m-d');

            $data['start_time'] = $data['starts_at']
                ->setTimezone('Asia/Jakarta')
                ->format('H:i:s');

            $data['end_time'] = $data['ends_at']
                ->setTimezone('Asia/Jakarta')
                ->format('H:i:s');

            $event->forceFill($data);
            app(PackageService::class)->assertFits($event, $snapshot);
            if ($event->exists && $event->isDirty(['starts_at', 'ends_at', 'registration_deadline'])) {
                foreach ($event->positions()->orderBy('id')->lockForUpdate()->get() as $position) {
                    if ($position->follows_event_schedule) {
                        $position->schedules()->delete();
                        $position->schedules()->create([
                            'starts_at' => $event->starts_at,
                            'ends_at' => $event->ends_at,
                        ]);
                    } else {
                        foreach ($position->schedules()->get() as $schedule) {
                            $this->assertScheduleWithinEvent($event, $schedule->starts_at, $schedule->ends_at);
                        }
                    }
                }
            }
            $event->save();

            if ($beforePackage !== $snapshot) {
                app(AuditService::class)->record($actor, 'event.package_selected', $event, ['after' => ['revision' => $event->revision]]);
            }

            app(AuditService::class)->record(
                $actor,
                'event.saved',
                $event,
                [
                    'after' => [
                        'status' => $event->status,
                        'revision' => $event->revision,
                    ],
                ]
            );

            return $event->fresh();
        }, 3);
    }

    public function position(
        Event $event,
        User $actor,
        array $input,
        ?EventPosition $position = null
    ): EventPosition {
        return DB::transaction(function () use (
            $event,
            $actor,
            $input,
            $position
        ) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            $event = Event::lockForUpdate()->findOrFail($event->id);

            Gate::forUser($actor)->authorize('update', $event);

            $this->editable($event);

            if ($position) {
                $position = $event->positions()
                    ->lockForUpdate()
                    ->findOrFail($position->id);
            }

            // Legacy callers supplying schedules retain custom mode.
            $input['follows_event_schedule'] ??= $position?->follows_event_schedule
                ?? ! array_key_exists('schedules', $input);
            $followsEvent = filter_var($input['follows_event_schedule'], FILTER_VALIDATE_BOOLEAN);

            $data = Validator::make($input, [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:5000',
                'quota' => 'required|integer|min:1|max:100000',
                'required_full_availability' => 'required|boolean',
                'required_same_city' => 'required|boolean',
                'skills' => 'required|array|min:1|max:100',
                'skills.*.skill_id' => [
                    'required',
                    'distinct',
                    Rule::exists('skills', 'id')->where('is_active', true),
                ],
                'skills.*.minimum_level' => 'required|in:beginner,intermediate,advanced,expert',
                'skills.*.is_required' => 'required|boolean',
                'follows_event_schedule' => 'required|boolean',
                'schedules' => [Rule::excludeIf($followsEvent), 'required', 'array', 'min:1', 'max:100'],
                'schedules.*.starts_at' => [Rule::excludeIf($followsEvent), 'required', 'date_format:Y-m-d\TH:i'],
                'schedules.*.ends_at' => [Rule::excludeIf($followsEvent), 'required', 'date_format:Y-m-d\TH:i'],
                'requirements' => 'sometimes|array|max:50',
                'requirements.*.name' => 'required|string|max:255',
                'requirements.*.description' => 'nullable|string|max:2000',
                'requirements.*.kind' => 'required|in:manual,document',
                'requirements.*.document_type' => 'nullable|in:cv,supporting',
                'requirements.*.is_required' => 'required|boolean',
            ])->validate();

            if (
                ! $position &&
                $event->package_snapshot &&
                $event->positions()->count() >=
                    $event->package_snapshot['max_positions']
            ) {
                throw ValidationException::withMessages([
                    'quota' => 'Batas posisi paket tercapai.',
                ]);
            }

            $schedules = [];

            if ($followsEvent) {
                $schedules[] = ['starts_at' => $event->starts_at, 'ends_at' => $event->ends_at];
            }

            foreach ($data['schedules'] ?? [] as $row) {
                $s = CarbonImmutable::createFromFormat(
                    '!Y-m-d\TH:i',
                    $row['starts_at'],
                    'Asia/Jakarta'
                )->utc();

                $e = CarbonImmutable::createFromFormat(
                    '!Y-m-d\TH:i',
                    $row['ends_at'],
                    'Asia/Jakarta'
                )->utc();

                $this->assertScheduleWithinEvent($event, $s, $e);

                $schedules[$s->timestamp.':'.$e->timestamp] = [
                    'starts_at' => $s,
                    'ends_at' => $e,
                ];
            }

            foreach ($data['requirements'] ?? [] as $r) {
                if (
                    $r['kind'] === 'document' &&
                    empty($r['document_type'])
                ) {
                    throw ValidationException::withMessages([
                        'requirements' => 'Jenis dokumen wajib dipilih untuk syarat dokumen.',
                    ]);
                }
            }

            $position ??= new EventPosition([
                'event_id' => $event->id,
            ]);

            $position
                ->fill(
                    collect($data)
                        ->only([
                            'name',
                            'description',
                            'quota',
                            'required_full_availability',
                            'required_same_city',
                            'follows_event_schedule',
                        ])
                        ->all()
                )
                ->save();

            $position->positionSkills()->delete();
            $position->positionSkills()->createMany($data['skills']);

            $position->schedules()->delete();
            $position->schedules()->createMany(
                array_values($schedules)
            );

            $position->requirements()->delete();
            $position->requirements()->createMany(
                $data['requirements'] ?? []
            );

            $event->forceFill([
                'status' => 'draft',
                'revision' => $event->revision + 1,
            ])->save();

            app(AuditService::class)->record(
                $actor,
                'position.saved',
                $position
            );

            return $position;
        }, 3);
    }

    private function assertScheduleWithinEvent(Event $event, $start, $end): void
    {
        if ($end <= $start || $start < $event->starts_at || $end > $event->ends_at || $start < $event->registration_deadline) {
            throw ValidationException::withMessages([
                'schedules' => 'Jadwal khusus posisi harus berdurasi positif, dalam rentang event dan setelah deadline. Sesuaikan jadwal posisi sebelum mengubah rentang event.',
            ]);
        }
    }

    public function removePosition(
        Event $event,
        EventPosition $position,
        User $actor
    ): void {
        DB::transaction(function () use ($event, $position, $actor) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            $event = Event::lockForUpdate()->findOrFail($event->id);

            Gate::forUser($actor)->authorize('update', $event);

            $this->editable($event);

            $position = $event->positions()
                ->lockForUpdate()
                ->findOrFail($position->id);

            app(AuditService::class)->record(
                $actor,
                'position.deleted',
                $position
            );

            if ($position->assessments()->where('is_published', true)->exists()) {
                throw ValidationException::withMessages(['assessment' => 'Posisi dengan assessment published tidak dapat dihapus.']);
            }
            $position->assessments()->delete();
            $position->delete();

            $event->forceFill([
                'status' => 'draft',
                'revision' => $event->revision + 1,
            ])->save();
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
                throw ValidationException::withMessages([
                    'event' => 'Pendaftaran sudah ditutup.',
                ]);
            }

            $event->forceFill([
                'status' => 'pending',
                'submitted_at' => now(),
                'revision' => $event->revision + 1,
            ])->save();

            app(AuditService::class)->record(
                $actor,
                'event.submitted',
                $event,
                [
                    'after' => [
                        'status' => 'pending',
                        'revision' => $event->revision,
                    ],
                ]
            );
        }, 3);
    }
}
