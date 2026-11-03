<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\OrganizerDocument;
use App\Models\OrganizerProfile;
use App\Models\User;
use App\Rules\PhoneNumber;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        $profileExists = OrganizerProfile::where('user_id', $user->id)->exists();

        $ktp = $request->file('ktp');
        if ($ktp && ! $ktp->isValid()) {
            $error = $ktp->getError();
            // Record only transport diagnostics, never the identity document or its name.
            Log::warning('organizer.ktp.upload_failed', [
                'php_upload_error' => $error,
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ]);
            throw ValidationException::withMessages([
                'ktp' => match ($error) {
                    UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'KTP ditolak oleh batas upload PHP server ('.ini_get('upload_max_filesize').'). Pilih berkas lebih kecil atau hubungi pengelola.',
                    UPLOAD_ERR_PARTIAL => 'Upload KTP terputus sebelum seluruh berkas diterima. Pilih ulang berkas dan coba kembali.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara upload pada server tidak tersedia. Hubungi pengelola untuk memperbaiki konfigurasi PHP.',
                    UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis berkas sementara KTP. Hubungi pengelola untuk memeriksa izin folder dan ruang penyimpanan.',
                    UPLOAD_ERR_EXTENSION => 'Upload KTP dihentikan oleh ekstensi PHP pada server. Hubungi pengelola.',
                    default => 'Server belum menerima berkas KTP yang valid. Pilih ulang berkas dan coba kembali.',
                },
            ]);
        }

        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['bail', 'required', new PhoneNumber],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'ktp' => [
                $profileExists ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ], [
            'ktp.required' => 'KTP wajib diupload untuk pengajuan profil pertama kali.',
        ]);

        $newPath = null;
        $oldPaths = [];

        if ($request->hasFile('ktp')) {
            $newPath = $request->file('ktp')->store(
                'organizers/'.$user->id.'/documents', 'local'
            );

            if (! $newPath) {
                throw ValidationException::withMessages([
                    'ktp' => 'KTP baru gagal disimpan. Silakan coba kembali.',
                ]);
            }
        }

        try {
            DB::transaction(function () use (
                $user,
                $validated,
                $request,
                $newPath,
                &$oldPaths
            ) {
                $locked = User::whereKey($user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_unless(
                    $locked->is_active && $locked->role === 'organizer',
                    403
                );

                $previous = $locked->organizerProfile;
                $identityChanged = $previous && ($newPath || collect(['organization_name', 'address', 'city'])
                    ->contains(fn ($field) => $validated[$field] !== $previous->$field));
                if ($identityChanged && $locked->organizer_status === 'active' && ! $request->boolean('confirm_reverification')) {
                    throw ValidationException::withMessages(['confirm_reverification' => 'Konfirmasikan peninjauan ulang identitas. Pengelolaan event ditunda sampai Admin menyetujui kembali.']);
                }

                $profile = $locked->organizerProfile()->updateOrCreate(
                    ['user_id' => $locked->id],
                    [
                        'organization_name' => $validated['organization_name'],
                        'contact_person' => $validated['contact_person'],
                        'phone' => $validated['phone'],
                        'email' => $locked->email,
                        'address' => $validated['address'],
                        'city' => $validated['city'],
                        'website' => $validated['website'] ?? null,
                        'description' => $validated['description'],
                    ]
                );
                if ($newPath) {
                    $documents = $locked->organizerDocuments()
                        ->where('document_type', 'ktp')
                        ->get();

                    $oldPaths = $documents
                        ->pluck('file_path')
                        ->filter()
                        ->reject(fn ($path) => $path === $newPath)
                        ->unique()
                        ->values()
                        ->all();

                    foreach ($documents as $document) {
                        $document->delete();
                    }

                    $locked->organizerDocuments()->create([
                        'document_type' => 'ktp',
                        'document_name' => $request->file('ktp')->getClientOriginalName(),
                        'file_path' => $newPath,
                    ]);
                }

                if (! $previous || $identityChanged || $locked->organizer_status === 'inactive') {
                    $locked->forceFill(['organizer_status' => 'pending', 'organizer_verification_note' => null])->save();
                }
                $locked->forceFill(['organizer_revision' => $locked->organizer_revision + 1])->save();
                if ($locked->organizer_status === 'pending') {
                    foreach (User::where('role', 'admin')->where('is_active', true)->get() as $admin) {
                        app(NotificationService::class)->enqueue('organizer.submitted:'.$locked->id.':'.$locked->organizer_revision.':'.$admin->id,
                            $admin, ['type' => 'organizer.submitted']);
                    }
                }

                app(AuditService::class)->record(
                    $locked,
                    'organizer.profile.updated',
                    $profile,
                    [
                        'after' => [
                            'changed_fields' => array_keys($validated),
                        ],
                    ]
                );
            });
        } catch (\Throwable $e) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }

            throw $e;
        }

        DB::afterCommit(function () use ($oldPaths) {
            foreach ($oldPaths as $oldPath) {
                Storage::disk('local')->delete($oldPath);
            }
        });

        if (! $profileExists || $user->fresh()->organizer_status === 'pending') {
            return redirect()
                ->route('organizer.profile.pending')
                ->with(
                    'success',
                    'Data Organizer berhasil dikirim dan sedang menunggu verifikasi admin.'
                );
        }

        return redirect()
            ->route('organizer.profile.edit')
            ->with('success', 'Profil organisasi berhasil diperbarui.');
    }

    public function pending(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        Gate::authorize('updateProfile', $user);

        if ($user->organizer_status === 'active') {
            return redirect()->route('organizer.activity.index');
        }

        return view('organizer.profile.pending', compact('user'));
    }

    public function viewDocument(Request $request, OrganizerDocument $document)
    {
        abort_unless(
            $document->user_id === $request->user()->id,
            403
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
