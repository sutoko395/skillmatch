<?php

namespace App\Services;

use App\Models\NotificationOutbox;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function enqueue(string $eventKey, User $recipient, array $payload): NotificationOutbox
    {
        if (DB::transactionLevel() < 1) {
            throw new \LogicException('Notifications must share the business transaction.');
        }
        if (strlen($eventKey) > 191 || ! preg_match('/\A[a-z][a-z0-9_.:-]+\z/', $eventKey)
            || ! preg_match('/\A[a-z][a-z0-9_.]+\z/', $payload['type'] ?? '')) {
            throw new \InvalidArgumentException('Invalid notification key or type.');
        }

        $row = NotificationOutbox::deduplicate($eventKey, [
            'recipient_id' => $recipient->id, 'application_id' => $payload['application_id'] ?? null,
            'channel' => 'database', 'type' => $payload['type'],
            'data' => array_intersect_key($payload, array_flip(['type', 'event_id', 'application_id', 'order_id', 'status'])),
            'status' => 'sent', 'available_at' => now(), 'delivered_at' => now(),
        ]);
        if ($row->recipient_id !== $recipient->id || $row->channel !== 'database' || $row->type !== $payload['type']) {
            throw new \LogicException('Notification key belongs to a different recipient or channel.');
        }

        return $row;
    }
}
