<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganizerDocument;
use App\Models\User;
use App\Services\AccountManagementService;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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
            'verification_note' => ['required_if:organizer_status,inactive', 'nullable', 'string', 'min:5', 'max:1000'],
            'revision' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $status = $validated['organizer_status'];

        DB::transaction(function () use ($user, $status, $validated) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ((int) $validated['revision'] !== (int) $locked->organizer_revision) {
                throw ValidationException::withMessages(['revision' => 'Pengajuan telah berubah. Muat ulang sebelum memutuskan.']);
            }
            $document = $locked->organizerDocuments()->where('document_type', 'ktp')->latest('id')->first();
            if ($status === 'active' && (! $locked->organizerProfile || ! $document || ! Storage::disk('local')->exists($document->file_path))) {
                throw ValidationException::withMessages(['organizer_status' => 'Lengkapi profil dan KTP sebelum persetujuan.']);
            }
            $before = $locked->organizer_status;
            $beforeActive = $locked->is_active;
            $locked->forceFill(['organizer_status' => $status,
                'is_active' => $validated['is_active'] ?? $locked->is_active,
                'organizer_verification_note' => $validated['verification_note'] ?? null,
                'organizer_revision' => $locked->organizer_revision + 1])->save();
            app(AuditService::class)->record(auth()->user(), 'organizer.reviewed', $locked,
                ['before' => ['organizer_status' => $before, 'is_active' => $beforeActive],
                    'after' => ['organizer_status' => $status, 'is_active' => $locked->is_active, 'revision' => $locked->organizer_revision]]);
            app(NotificationService::class)->enqueue('organizer.reviewed:'.$locked->id.':'.$locked->organizer_revision.':'.$locked->id,
                $locked, ['type' => 'organizer.reviewed', 'status' => $status]);
        }, 3);

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

        Gate::authorize('manageOrganizer', $user);

        app(AccountManagementService::class)->delete($user, auth()->user());

        return back()->with(
            'success',
            'Akun Organizer berhasil dihapus.'
        );
    }

    public function viewDocument(Request $request, OrganizerDocument $document)
    {
        Gate::authorize('manageOrganizer', $document->user);

        abort_unless(
            $document->document_type === 'ktp',
            404
        );

        abort_unless(
            Storage::disk('local')->exists($document->file_path),
            404
        );

        return Storage::disk('local')->response(
            $document->file_path,
            $document->document_name
        );
    }
}
