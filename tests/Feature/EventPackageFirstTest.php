<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Event;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class EventPackageFirstTest extends DatabaseTestCase
{
    use A2Fixture;

    private function plan(string $name, int $days, int $price): Package
    {
        return Package::create(['name' => $name, 'price' => $price, 'max_positions' => 2, 'max_applications' => 100, 'max_registration_days' => $days, 'is_active' => true]);
    }

    private function input(Event $event, Package $package, int $days): array
    {
        $opens = now()->addDay()->startOfMinute();

        return $event->only(['title', 'description', 'location', 'city_id', 'category_id']) + [
            'package_id' => $package->id,
            'registration_opens_at' => $opens->format('Y-m-d\TH:i'),
            'registration_deadline' => $opens->copy()->addDays($days)->format('Y-m-d\TH:i'),
            'starts_at' => $opens->copy()->addDays(75)->format('Y-m-d\TH:i'),
            'ends_at' => $opens->copy()->addDays(75)->addHours(3)->format('Y-m-d\TH:i'),
        ];
    }

    public function test_limits_are_exact_and_over_limit_creation_rolls_back_without_order(): void
    {
        [$owner, $event] = $this->fixture();
        $this->actingAs($owner);
        foreach ([['Free', 7, 0], ['Standard', 30, 30000], ['Premium', 60, 50000]] as [$name, $days, $price]) {
            $package = $this->plan($name, $days, $price);
            $data = $this->input($event, $package, $days);
            $beforeOrders = Order::count();
            $this->post('/organizer/events', $data)->assertRedirect()->assertSessionHasNoErrors();
            $created = Event::latest('id')->firstOrFail();
            $this->assertSame($package->snapshot(), $created->package_snapshot);
            $this->assertSame('draft', $created->status);
            $this->assertSame('unpublished', $created->publication_status);
            $this->assertNull($created->entitlement);
            $this->assertSame($beforeOrders, Order::count());
            $data['registration_deadline'] = now()->addDay()->startOfMinute()->addDays($days)->addMinute()->format('Y-m-d\TH:i');
            $beforeEvents = Event::count();
            $beforeAudit = AuditLog::count();
            $this->postJson('/organizer/events', $data + ['package_snapshot' => ['max_registration_days' => 365]])
                ->assertUnprocessable()->assertJsonValidationErrors('registration_deadline');
            $this->assertSame($beforeEvents, Event::count());
            $this->assertSame($beforeAudit, AuditLog::count());
        }
    }

    public function test_switching_package_allows_longer_registration_and_price_is_server_owned(): void
    {
        [$owner, $event] = $this->fixture();
        $free = $this->plan('Free', 7, 0);
        $standard = $this->plan('Standard', 30, 30000);
        $data = $this->input($event, $free, 8);
        $this->actingAs($owner)->postJson('/organizer/events', $data)->assertUnprocessable()->assertJsonValidationErrors('registration_deadline');
        $data['package_id'] = $standard->id;
        $this->post('/organizer/events', $data + ['price' => 0, 'package_snapshot' => ['price' => 0]])->assertRedirect()->assertSessionHasNoErrors();
        $created = Event::latest('id')->firstOrFail();
        $this->assertSame(30000, $created->package_snapshot['price']);
        $this->assertSame(30, $created->package_snapshot['max_registration_days']);
        $this->assertNull($created->entitlement);
        $this->assertSame(1, AuditLog::where('action', 'event.package_selected')->where('subject_id', $created->id)->count());
    }

    public function test_missing_or_inactive_package_and_unauthorized_owner_are_rejected(): void
    {
        [$owner, $event, , $package] = $this->fixture();
        $package->update(['is_active' => false]);
        $data = $this->input($event, $package, 7);
        $this->actingAs($owner);
        foreach ([null, 999999999, $package->id] as $id) {
            $this->postJson('/organizer/events', array_replace($data, ['package_id' => $id]))
                ->assertUnprocessable()->assertJsonValidationErrors('package_id');
        }
        $this->actingAs(User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']))
            ->putJson("/organizer/events/{$event->id}", $data)->assertForbidden();
    }

    public function test_edit_preserves_snapshot_and_can_change_package_and_dates_atomically(): void
    {
        [$owner, $event, , $package] = $this->fixture();
        $snapshot = $event->package_snapshot;
        $package->update(['price' => 999999, 'max_registration_days' => 1, 'is_active' => false]);
        $data = $this->input($event, $package, 10);
        // Keep existing position schedule inside the original event range.
        foreach (['starts_at', 'ends_at'] as $field) {
            $data[$field] = $event->{$field}->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i');
        }
        $data['registration_opens_at'] = $event->registration_opens_at->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i');
        $data['registration_deadline'] = $event->registration_deadline->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i');
        $this->actingAs($owner)->put("/organizer/events/{$event->id}", $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($snapshot, $event->fresh()->package_snapshot);
        $this->get("/organizer/events/{$event->id}/edit")->assertOk()->assertSee('maksimal 30 hari pendaftaran');
        $free = $this->plan('Free', 7, 0);
        $beforeAudit = AuditLog::count();
        $this->putJson("/organizer/events/{$event->id}", array_replace($data, ['package_id' => $free->id]))
            ->assertUnprocessable()->assertJsonValidationErrors('registration_deadline');
        $this->assertSame($snapshot, $event->fresh()->package_snapshot);
        $this->assertSame($beforeAudit, AuditLog::count());
        $standard = $this->plan('Standard', 30, 30000);
        $this->put("/organizer/events/{$event->id}", array_replace($data, ['package_id' => $standard->id]))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($standard->snapshot(), $event->fresh()->package_snapshot);
        $event->forceFill(['published_at' => now()])->save();
        $this->putJson("/organizer/events/{$event->id}", $data)->assertUnprocessable()->assertJsonValidationErrors('event');
    }

    public function test_package_change_rejects_existing_position_count_before_writing(): void
    {
        [$owner, $event, $position] = $this->fixture();
        $position->replicate()->save();
        $small = $this->plan('Paket kecil', 30, 0);
        $small->update(['max_positions' => 1]);
        $input = $event->only(['title', 'description', 'location', 'city_id', 'category_id']);
        $input['package_id'] = $small->id;
        foreach (['starts_at', 'ends_at', 'registration_opens_at', 'registration_deadline'] as $field) {
            $input[$field] = $event->{$field}->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i');
        }
        $snapshot = $event->package_snapshot;
        $beforeAudit = AuditLog::count();
        $this->actingAs($owner)->putJson("/organizer/events/{$event->id}", $input)->assertUnprocessable()->assertJsonValidationErrors('package_id');
        $this->assertSame($snapshot, $event->fresh()->package_snapshot);
        $this->assertSame($beforeAudit, AuditLog::count());
    }

    public function test_legacy_draft_without_snapshot_must_choose_package_before_edit_save(): void
    {
        [$owner, $event, , $package] = $this->fixture();
        $event->forceFill(['package_snapshot' => null])->save();
        $input = $event->only(['title', 'description', 'location', 'city_id', 'category_id']);
        foreach (['starts_at', 'ends_at', 'registration_opens_at', 'registration_deadline'] as $field) {
            $input[$field] = $event->{$field}->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i');
        }
        $this->actingAs($owner)->putJson("/organizer/events/{$event->id}", $input)->assertUnprocessable()->assertJsonValidationErrors('package_id');
        $this->assertNull($event->fresh()->package_snapshot);
        $this->put("/organizer/events/{$event->id}", $input + ['package_id' => $package->id])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($package->snapshot(), $event->fresh()->package_snapshot);
    }
}
