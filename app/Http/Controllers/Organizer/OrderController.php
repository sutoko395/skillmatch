<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function store(Request $r, Event $event, PaymentService $service)
    {
        $order = $service->createOrder($event, $r->user());

        return redirect()->route('organizer.orders.show', $order);
    }

    public function show(Request $request, Order $order)
    {
        Gate::authorize('view', $order);

        // A return marker triggers a server check, never a payment transition from browser data.
        $checkPayment = $request->query('check_payment') === '1';

        return view('organizer.orders.show', compact('order', 'checkPayment'));
    }

    public function checkout(Request $r, Order $order, PaymentService $service)
    {
        Gate::authorize('view', $order);
        $service->checkout($order, $r->user());

        return redirect()->route('organizer.orders.show', $order)->with('success', 'Checkout Sandbox siap.');
    }

    public function sync(Request $request, Order $order, PaymentService $service)
    {
        Gate::authorize('view', $order);
        $result = $service->sync($order);

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $result->status,
                'paid' => (bool) $result->paid_at,
                'activated' => (bool) $result->activated_at,
                'requires_follow_up' => $result->requires_follow_up,
            ]);
        }

        return back()->with('success', 'Status pembayaran diperiksa melalui server.');
    }
}
