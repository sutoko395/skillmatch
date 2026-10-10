<?php

namespace App\Http\Controllers\Volunteer;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Services\DocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VolunteerDocumentController extends Controller
{
    public function __construct(
        protected DocumentStorageService $documentStorageService
    ) {}

    public function store(Request $request, Application $application)
    {
        $request->validate([
            'document_type' => 'required|string|in:cv,supporting',
            'file' => 'required|file|max:5120',
        ], [
            'document_type.required' => 'Pilih tipe dokumen.',
            'document_type.in' => 'Tipe dokumen harus CV atau dokumen pendukung.',
            'file.required' => 'Pilih file dokumen yang ingin diunggah.',
            'file.max' => 'Ukuran file tidak boleh melebihi 5 MB.',
        ]);

        $this->documentStorageService->store(
            $request->file('file'),
            [
                'type' => 'application',
                'application' => $application,
                'document_type' => $request->input('document_type'),
            ],
            Auth::user()
        );

        return redirect()
            ->route('volunteer.applications.show', $application)
            ->with('success', 'Dokumen berhasil diunggah.');
    }

    public function destroy(ApplicationDocument $document)
    {
        $application = $document->application;

        $this->documentStorageService->delete($document, Auth::user());

        return redirect()
            ->route('volunteer.applications.show', $application)
            ->with('success', 'Dokumen berhasil dihapus.');
    }
}
