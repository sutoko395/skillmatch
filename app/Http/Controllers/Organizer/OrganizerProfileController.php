<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\OrganizerDocument;
use App\Models\OrganizerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrganizerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

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

        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'documents' => ['required', 'array', 'min:1'],
            'documents.*.type' => ['required', 'string', 'max:100'],
            'documents.*.name' => ['required', 'string', 'max:255'],
            'documents.*.file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        OrganizerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'organization_name' => $validated['organization_name'],
                'contact_person' => $validated['contact_person'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'address' => $validated['address'],
                'city' => $validated['city'],
                'website' => $validated['website'] ?? null,
                'description' => $validated['description'],
            ]
        );

        foreach ($validated['documents'] as $document) {
            $path = $document['file']->store('organizer-documents', 'public');

            OrganizerDocument::create([
                'user_id' => $user->id,
                'document_type' => $document['type'],
                'document_name' => $document['name'],
                'file_path' => $path,
            ]);
        }

        $user->update([
            'organizer_status' => 'pending',
            'is_active' => false,
        ]);

        return redirect()
            ->route('organizer.profile.pending')
            ->with('success', 'Pengajuan verifikasi berhasil dikirim.');
    }

    public function pending(Request $request)
    {
        $user = $request->user();

        if ($user->organizer_status === 'active') {
            return redirect()->route('organizer.dashboard');
        }

        return view('organizer.profile.pending', compact('user'));
    }
}