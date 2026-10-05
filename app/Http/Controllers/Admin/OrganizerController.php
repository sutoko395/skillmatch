<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrganizerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();
        $perPage = (int) $request->input('per_page', 10);

        if (! in_array($perPage, [10, 50, 100])) {
            $perPage = 10;
        }

        $organizers = User::query()
            ->where('role', 'organizer')
            ->with([
                'organizerProfile',
                'organizerDocuments',
            ])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['pending', 'active', 'inactive']), function ($query) use ($status) {
                $query->where('organizer_status', $status);
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $totalOrganizers = User::where('role', 'organizer')->count();

        $pendingOrganizers = User::where('role', 'organizer')
            ->where('organizer_status', 'pending')
            ->count();

        $activeOrganizers = User::where('role', 'organizer')
            ->where('organizer_status', 'active')
            ->count();

        $inactiveOrganizers = User::where('role', 'organizer')
            ->where('organizer_status', 'inactive')
            ->count();

        return view('admin.organizers.index', compact(
            'organizers',
            'totalOrganizers',
            'pendingOrganizers',
            'activeOrganizers',
            'inactiveOrganizers',
            'search',
            'status',
            'perPage'
        ));
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('manageOrganizer', $user);
        if ($user->role !== 'organizer') {
            abort(404);
        }

        $validated = $request->validate([
            'organizer_status' => [
                'required',
                'in:pending,active,inactive',
            ],
        ]);

        $status = $validated['organizer_status'];

        $user->update([
            'organizer_status' => $status,
        ]);

        return back()->with(
            'success',
            'Status Organizer berhasil diperbarui.'
        );
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->role !== 'organizer') {
            abort(404);
        }

        Gate::authorize('delete', $user);

        return back()->with(
            'success',
            'Akun Organizer berhasil dihapus.'
        );
    }
}
