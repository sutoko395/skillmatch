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

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        return view('organizer.orders.show', compact('order'));
    }

    public function checkout(Request $r, Order $order, PaymentService $service)
    {
        Gate::authorize('view', $order);
        $service->checkout($order, $r->user());

        return redirect()->route('organizer.orders.show', $order)->with('success', 'Checkout Sandbox siap.');
    }

    public function sync(Order $order, PaymentService $service)
    {
        Gate::authorize('view', $order);
        $service->sync($order);

        return back()->with('success', 'Status pembayaran diperiksa melalui server.');
    }
}
