<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Package;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $r)
    {
        Gate::authorize('manage', Package::class);
        $r->validate(['status' => 'nullable|in:pending,paid,failed,expired,cancelled,review_required', 'search' => 'nullable|string|max:100']);

        return view('admin.orders.index', ['orders' => Order::with('event')->when($r->filled('status'), fn ($q) => $q->where('status', $r->string('status')->toString()))->when($r->filled('search'), fn ($q) => $q->where('order_ref', 'like', '%'.$r->string('search')->toString().'%'))->latest()->paginate(20)->withQueryString()]);
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        return view('admin.orders.show', compact('order'));
    }

    public function sync(Order $order, PaymentService $service)
    {
        Gate::authorize('view', $order);
        $service->sync($order);

        return back()->with('success', 'Status gateway diperiksa.');
    }
}
