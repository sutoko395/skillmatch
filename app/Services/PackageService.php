<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PackageService
{
    public function save(User $actor, array $input, ?Package $package = null): Package
    {
        return DB::transaction(function () use ($actor, $input, $package) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            Gate::forUser($actor)->authorize('manage', Package::class);
            $package = $package ? Package::lockForUpdate()->findOrFail($package->id) : new Package;
            $data = Validator::make($input, [
                'name' => ['required', 'string', 'max:255', Rule::unique('packages')->ignore($package->id)],
                'price' => 'required|integer|min:0|max:1000000000', 'max_positions' => 'required|integer|min:1|max:1000',
                'max_applications' => 'required|integer|min:1|max:1000000', 'max_registration_days' => 'required|integer|min:1|max:365',
                'is_active' => 'required|boolean',
            ])->validate();
            $before = $package->only(['price', 'max_positions', 'max_applications', 'max_registration_days', 'is_active']);
            $package->fill($data)->save();
            app(AuditService::class)->record($actor, 'package.saved', $package, ['before' => $before, 'after' => $package->only(['price', 'max_positions', 'max_applications', 'max_registration_days', 'is_active'])]);

            return $package;
        }, 3);
    }

    public function select(Event $event, User $actor, int $packageId): void
    {
        DB::transaction(function () use ($event, $actor, $packageId) {
            $actor = User::lockForUpdate()->findOrFail($actor->id);
            $event = Event::lockForUpdate()->findOrFail($event->id);
            Gate::forUser($actor)->authorize('update', $event);
            app(EventService::class)->editable($event);
            $package = Package::where('is_active', true)->lockForUpdate()->findOrFail($packageId);
            $this->assertFits($event, $package->snapshot());
            $event->forceFill(['package_snapshot' => $package->snapshot(), 'status' => 'draft', 'revision' => $event->revision + 1])->save();
            app(AuditService::class)->record($actor, 'event.package_selected', $event, ['after' => ['revision' => $event->revision]]);
        }, 3);
    }

    public function assertFits(Event $event, array $snapshot): void
    {
        if ($event->exists && $event->positions()->count() > $snapshot['max_positions']) {
            throw ValidationException::withMessages(['package_id' => 'Jumlah posisi melebihi batas paket. Pilih paket dengan kuota lebih besar.']);
        }
        if ($event->registration_opens_at && $event->registration_deadline
            && $event->registration_opens_at->diffInSeconds($event->registration_deadline) > $snapshot['max_registration_days'] * 86400) {
            throw ValidationException::withMessages(['registration_deadline' => "Durasi pendaftaran melebihi batas paket {$snapshot['name']} ({$snapshot['max_registration_days']} hari). Pilih paket dengan durasi lebih besar atau pendekkan jadwal pendaftaran."]);
        }
    }
}
