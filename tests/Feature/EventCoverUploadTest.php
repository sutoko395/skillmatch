<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\EventPublicationService;
use App\Services\EventService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\DatabaseTestCase;
use Tests\Support\A2Fixture;

class EventCoverUploadTest extends DatabaseTestCase
{
    use A2Fixture;

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('cover.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWQAAAABJRU5ErkJggg=='));
    }

    private function payload($event): array
    {
        $data = $event->only(['title', 'description', 'location', 'city_id', 'category_id']);
        $data['package_id'] = $event->package_snapshot['id'];
        foreach (['starts_at', 'ends_at', 'registration_opens_at', 'registration_deadline', 'assessment_deadline', 'decision_deadline'] as $field) {
            $data[$field] = $event->$field?->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i');
        }

        return $data;
    }

    public function test_owner_can_create_replace_keep_and_remove_a_cover(): void
    {
        Storage::fake('public');
        [$owner, $event] = $this->fixture();
        $input = $this->payload($event);
        $this->actingAs($owner)->post('/organizer/events', $input + ['cover_image' => $this->photo()])->assertRedirect();
        $created = $owner->events()->latest('id')->first();
        $first = $created->cover_image_path;
        $this->assertNotNull($first);
        Storage::disk('public')->assertExists($first);
        $this->assertSame('draft', $created->status);
        $this->assertSame('unpublished', $created->publication_status);
        $this->put("/organizer/events/{$created->id}", $input)->assertRedirect();
        $this->assertSame($first, $created->fresh()->cover_image_path);
        $this->put("/organizer/events/{$created->id}", $input + ['cover_image' => $this->photo()])->assertRedirect();
        $second = $created->fresh()->cover_image_path;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertExists($second);
        $this->get('/organizer/events')->assertOk()->assertSee(route('events.cover', $created));
        $this->get(route('events.cover', $created))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->app['auth']->forgetGuards();
        $this->get(route('events.cover', $created))->assertNotFound();
        $this->actingAs($owner);
        $this->put("/organizer/events/{$created->id}", $input + ['remove_cover_image' => '1'])->assertRedirect();
        $this->assertNull($created->fresh()->cover_image_path);
    }

    public function test_invalid_upload_and_other_owner_cannot_change_cover(): void
    {
        Storage::fake('public');
        [$owner, $event] = $this->fixture();
        $input = $this->payload($event);
        foreach ([UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml'), UploadedFile::fake()->create('big.jpg', 5121, 'image/jpeg')] as $file) {
            $this->actingAs($owner)->putJson("/organizer/events/{$event->id}", $input + ['cover_image' => $file])->assertUnprocessable()->assertJsonValidationErrors('cover_image');
        }
        $other = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->actingAs($other)->putJson("/organizer/events/{$event->id}", $input + ['cover_image' => $this->photo()])->assertForbidden();
        $this->assertNull($event->fresh()->cover_image_path);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_failed_event_validation_cleans_uploaded_file_and_rejects_storage_path(): void
    {
        Storage::fake('public');
        [$owner, $event] = $this->fixture();
        $this->actingAs($owner)->putJson("/organizer/events/{$event->id}", array_replace($this->payload($event), ['title' => '', 'cover_image' => $this->photo()]))->assertUnprocessable();
        $this->assertSame([], Storage::disk('public')->allFiles());
        app(EventService::class)->save($owner, $this->payload($event) + ['cover_image_path' => 'untrusted.jpg'], $event);
        $this->assertNull($event->fresh()->cover_image_path);
    }

    public function test_public_cover_follows_event_visibility_and_publication_locks_upload(): void
    {
        Storage::fake('public');
        $this->assessmentReady();
        [$owner, $event] = $this->fixture();
        $input = $this->payload($event);
        app(EventService::class)->save($owner, $input + ['cover_image' => $this->photo()], $event);
        $event->refresh();
        $this->get(route('events.cover', $event))->assertNotFound();
        $event->forceFill(['status' => 'approved'])->save();
        app(EventPublicationService::class)->publish($event, $owner);
        $this->get(route('events.cover', $event))->assertOk();
        $this->get('/events')->assertOk()->assertSee(route('events.cover', $event));
        $this->get(route('events.show', $event))->assertOk()->assertSee(route('events.cover', $event));
        $path = $event->fresh()->cover_image_path;
        $this->actingAs($owner)->putJson("/organizer/events/{$event->id}", $input + ['cover_image' => $this->photo()])->assertUnprocessable();
        $this->assertSame($path, $event->fresh()->cover_image_path);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }
    public function test_direct_cover_update_changes_only_cover_and_revision(): void
    {
        Storage::fake('public');
        [$owner, $event] = $this->fixture();
        $before = $event->getAttributes();
        $this->actingAs($owner)->patch(route('organizer.events.cover.update', $event), [
            'cover_image' => $this->photo(), 'title' => 'Must not replace title', 'status' => 'approved',
        ])->assertRedirect(route('organizer.events.show', $event));
        $after = $event->fresh();
        Storage::disk('public')->assertExists($after->cover_image_path);
        foreach (['title', 'description', 'status', 'publication_status', 'starts_at', 'ends_at', 'package_snapshot'] as $field) {
            $this->assertSame($before[$field], $after->getAttributes()[$field]);
        }
        $this->assertSame($event->revision + 1, $after->revision);
        $this->assertDatabaseHas('audit_logs', ['action' => 'event.cover_updated']);
        $first = $after->cover_image_path;
        $this->patch(route('organizer.events.cover.update', $event), ['cover_image' => $this->photo()])->assertRedirect();
        $this->assertNotSame($first, $event->fresh()->cover_image_path);
    }

    public function test_direct_cover_update_rejects_invalid_files_other_owners_and_locked_events(): void
    {
        Storage::fake('public');
        [$owner, $event] = $this->fixture();
        $url = route('organizer.events.cover.update', $event);
        $this->actingAs($owner)->patchJson($url, [])->assertUnprocessable()->assertJsonValidationErrors('cover_image');
        foreach ([UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml'), UploadedFile::fake()->create('big.jpg', 5121, 'image/jpeg')] as $file) {
            $this->patchJson($url, ['cover_image' => $file])->assertUnprocessable()->assertJsonValidationErrors('cover_image');
        }
        $other = User::factory()->create(['role' => 'organizer', 'organizer_status' => 'active']);
        $this->actingAs($other)->patchJson($url, ['cover_image' => $this->photo()])->assertForbidden();
        $this->actingAs(User::factory()->create())->patchJson($url, ['cover_image' => $this->photo()])->assertForbidden();
        $this->actingAs($owner);
        foreach ([['status' => 'pending'], ['status' => 'approved'], ['status' => 'draft', 'published_at' => now()]] as $state) {
            $event->forceFill($state)->save();
            $this->patchJson($url, ['cover_image' => $this->photo()])->assertUnprocessable()->assertJsonValidationErrors('event');
        }
        $this->assertNull($event->fresh()->cover_image_path);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

}
