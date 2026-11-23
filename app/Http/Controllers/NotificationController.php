<?php

namespace App\Http\Controllers;

use App\Models\NotificationOutbox;
use App\Services\NotificationReadService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request, NotificationReadService $reader)
    {
        $notifications = NotificationOutbox::where('recipient_id', $request->user()->id)
            ->where('channel', 'database')->latest('id')->paginate(20);

        $notificationItems = $reader->items($notifications->getCollection(), $request->user());

        return view('notifications.index', compact('notifications', 'notificationItems'));
    }

    public function read(Request $request, NotificationOutbox $notification)
    {
        abort_unless($notification->recipient_id === $request->user()->id && $notification->channel === 'database', 403);
        NotificationOutbox::whereKey($notification->id)->whereNull('read_at')->update(['read_at' => now()]);

        return redirect()->route('notifications.index')->with('success', 'Notifikasi ditandai sudah dibaca.');
    }
}
