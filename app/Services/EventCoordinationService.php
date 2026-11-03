<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Event;
use App\Models\EventCoordination;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EventCoordinationService
{
    public const FIELDS = ['group_url', 'pic_name', 'pic_contact'];

    public function forOrganizer(Event $event, User $actor): array
    {
        Gate::forUser($actor->fresh())->authorize('update', $event->fresh());

        return $this->details(EventCoordination::where('event_id', $event->id)->first());
    }

    public function forParticipant(Application $application, User $actor): ?array
    {
        $actor = $actor->fresh();
        // Query current authorization before fetching any private data, even with a stale model.
        $eligible = $actor && $actor->is_active && $actor->role === 'volunteer'
            && Application::whereKey($application->id)->where('volunteer_id', $actor->id)
                ->where('status', 'accepted')->whereHas('event', fn ($q) => $q->where('lifecycle_status', '!=', 'cancelled'))->exists();
        if (! $eligible) {
            return null;
        }

        return $this->details(EventCoordination::where('event_id', $application->event_id)->first());
    }

    private function details(?EventCoordination $row): array
    {
        return [
            'group_url' => $row?->group_url, 'pic_name' => $row?->pic_name,
            'pic_contact' => $row?->pic_contact, 'revision' => $row?->revision ?? 0,
        ];
    }

    public function update(Event $event, User $actor, array $input): void
    {
        DB::transaction(function () use ($event, $actor, $input) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            $event = Event::lockForUpdate()->findOrFail($event->id);
            Gate::forUser($actor)->authorize('update', $event);
            abort_if(in_array($event->lifecycle_status, ['completed', 'cancelled'], true), 409, 'Koordinasi event yang telah berakhir tidak dapat diubah.');
            $data = Validator::make($input, [
                'revision' => ['required', 'integer', 'min:0'],
                'group_url' => ['present', 'nullable', 'string', 'max:2048', 'url:https'],
                'pic_name' => ['present', 'nullable', 'string', 'max:100'],
                'pic_contact' => ['present', 'nullable', 'string', 'max:255'],
            ])->validate();
            foreach (self::FIELDS as $field) {
                $data[$field] = isset($data[$field]) ? (trim($data[$field]) ?: null) : null;
            }
            if ($data['group_url']) {
                $url = parse_url($data['group_url']);
                if (! $url || isset($url['user']) || isset($url['pass']) || preg_match('/[\x00-\x20\x7f]/', $data['group_url'])) {
                    throw ValidationException::withMessages(['group_url' => 'Gunakan tautan grup HTTPS yang valid tanpa kredensial.']);
                }
            }
            $row = EventCoordination::where('event_id', $event->id)->lockForUpdate()->first();
            $before = $this->details($row);
            $changed = array_values(array_filter(self::FIELDS, fn ($field) => $before[$field] !== $data[$field]));
            // An identical retry is safe; a differing stale form must never overwrite a newer version.
            if (! $changed) {
                return;
            }
            if ((int) $data['revision'] !== $before['revision']) {
                throw ValidationException::withMessages(['revision' => 'Koordinasi telah diperbarui. Muat ulang halaman sebelum menyimpan.']);
            }
            $row ??= new EventCoordination(['event_id' => $event->id]);
            $row->fill(array_intersect_key($data, array_flip(self::FIELDS)) + ['revision' => $before['revision'] + 1, 'updated_by' => $actor->id])->save();
            app(AuditService::class)->record($actor, 'event.coordination_updated', $event, [
                'before' => ['revision' => $before['revision']],
                'after' => ['revision' => $row->revision, 'changed_fields' => $changed],
            ]);
            Application::where('event_id', $event->id)->where('status', 'accepted')->with('volunteer')->orderBy('id')->chunkById(100, function ($applications) use ($event, $row) {
                foreach ($applications as $application) {
                    app(NotificationService::class)->enqueue('event.coordination_updated:'.$event->id.':'.$row->revision.':'.$application->volunteer_id, $application->volunteer, [
                        'type' => 'event.coordination_updated', 'event_id' => $event->id, 'application_id' => $application->id,
                    ]);
                }
            });
        }, 3);
    }
}
