<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Package;
use Carbon\CarbonImmutable;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $r)
    {
        Gate::authorize('manage', Package::class);
        $data = $r->validate([
            'status' => 'nullable|in:pending,paid,failed,expired,cancelled,review_required',
            'search' => 'nullable|string|max:100',
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => ['nullable', 'date_format:Y-m-d', \Illuminate\Validation\Rule::when($r->filled('date_from'), 'after_or_equal:date_from')],
        ]);
        $query = Order::with('event.organizer.organizerProfile');
        if ($r->filled('status')) $query->where('status', $data['status']);
        $search = trim($data['search'] ?? '');
        if ($search !== '') {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';
            $query->where(function ($q) use ($pattern) {
                $q->whereRaw("order_ref LIKE ? ESCAPE '!'", [$pattern])
                    ->orWhereHas('event', fn ($q) => $q->whereRaw("title LIKE ? ESCAPE '!'", [$pattern]))
                    ->orWhereHas('event.organizer', fn ($q) => $q->where(fn ($q) => $q
                        ->whereRaw("name LIKE ? ESCAPE '!'", [$pattern])->orWhereRaw("email LIKE ? ESCAPE '!'", [$pattern])))
                    ->orWhereHas('event.organizer.organizerProfile', fn ($q) => $q->whereRaw("organization_name LIKE ? ESCAPE '!'", [$pattern]));
            });
        }
        if ($r->filled('date_from')) $query->where('created_at', '>=', CarbonImmutable::createFromFormat('!Y-m-d', $data['date_from'], 'Asia/Jakarta')->utc());
        if ($r->filled('date_to')) $query->where('created_at', '<', CarbonImmutable::createFromFormat('!Y-m-d', $data['date_to'], 'Asia/Jakarta')->addDay()->utc());

        return view('admin.orders.index', ['orders' => $query->latest()->orderByDesc('id')->paginate(20)->withQueryString()]);
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        $order->load('event.organizer.organizerProfile');

        return view('admin.orders.show', compact('order'));
    }

    public function sync(Order $order, PaymentService $service)
    {
        Gate::authorize('view', $order);
        $service->sync($order);

        return back()->with('success', 'Status gateway diperiksa.');
    }
}
