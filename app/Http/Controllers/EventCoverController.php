<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventCoverController extends Controller
{
    public function __invoke(Request $request, Event $event)
    {
        $user = $request->user();
        $canPreview = $user && $user->is_active && (
            $user->role === 'admin' || ($user->role === 'organizer' && $event->organizer_id === $user->id)
        );
        abort_unless($canPreview || Event::publiclyVisible()->whereKey($event->id)->exists(), 404);
        abort_unless($event->cover_image_path && Storage::disk('public')->exists($event->cover_image_path), 404);

        return Storage::disk('public')->response($event->cover_image_path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
