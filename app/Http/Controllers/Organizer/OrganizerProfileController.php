<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\OrganizerDocument;
use App\Models\OrganizerProfile;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrganizerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        Gate::authorize('updateProfile', $user);

        $profile = OrganizerProfile::where('user_id', $user->id)->first();

        $documents = OrganizerDocument::where('user_id', $user->id)
            ->latest()
            ->get();

        return view('organizer.profile.edit', compact(
            'user',
            'profile',
            'documents'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        Gate::authorize('updateProfile', $user);

        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'documents' => ['prohibited'],
            'is_active' => ['prohibited'],
            'organizer_status' => ['prohibited'],
            'user_id' => ['prohibited'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->is_active && $locked->role === 'organizer', 403);
            $profile = $locked->organizerProfile;
            if ($profile && $locked->organizer_status === 'active' && $profile->organization_name !== $validated['organization_name']) {
                throw ValidationException::withMessages(['organization_name' => 'Perubahan identitas organisasi terverifikasi menunggu alur verifikasi ulang. Hubungi administrator.']);
            }
            $profile = $locked->organizerProfile()->updateOrCreate(['user_id' => $locked->id], $validated);
            app(AuditService::class)->record($locked, 'organizer.profile.updated', $profile, ['after' => ['changed_fields' => array_keys($validated)]]);
        });

        return redirect()->route('organizer.profile.edit')->with('success', 'Profil organisasi berhasil disimpan.');
    }

    public function pending(Request $request)
    {
        $user = $request->user();
        Gate::authorize('updateProfile', $user);

        if ($user->organizer_status === 'active') {
            return redirect()->route('organizer.dashboard');
        }

        return view('organizer.profile.pending', compact('user'));
    }
}
