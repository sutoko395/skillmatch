<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VolunteerController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();
        $perPage = (int) $request->input('per_page', 10);

        if (!in_array($perPage, [10, 50, 100])) {
            $perPage = 10;
        }

        $volunteers = User::query()
            ->where('role', 'volunteer')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['active', 'inactive']), function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $totalVolunteers = User::where('role', 'volunteer')->count();

        $activeVolunteers = User::where('role', 'volunteer')
            ->where('is_active', true)
            ->count();

        $inactiveVolunteers = User::where('role', 'volunteer')
            ->where('is_active', false)
            ->count();

        return view('admin.volunteers.index', compact(
            'volunteers',
            'totalVolunteers',
            'activeVolunteers',
            'inactiveVolunteers',
            'search',
            'status',
            'perPage'
        ));
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('manageVolunteer', $user);
        if ($user->role !== 'volunteer') {
            abort(404);
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        return back()->with(
            'success',
            $user->is_active
                ? 'Akun Volunteer berhasil diaktifkan.'
                : 'Akun Volunteer berhasil dinonaktifkan.'
        );
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->role !== 'volunteer') {
            abort(404);
        }

        \Illuminate\Support\Facades\Gate::authorize('delete', $user);

        return back()->with(
            'success',
            'Akun Volunteer berhasil dihapus.'
        );
    }
}