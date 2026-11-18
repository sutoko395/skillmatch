<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\City;
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
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrganizerProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        Gate::authorize('updateProfile', $user);

        $profile = OrganizerProfile::with('cityRecord.province')->where('user_id', $user->id)->first();
        $cities = City::where('is_active', true)->with('province')->orderBy('name')->get();

        $documents = OrganizerDocument::where('user_id', $user->id)
            ->latest()
            ->get();

        return view('organizer.profile.edit', compact(
            'user',
            'profile',
            'cities',
            'documents'
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        Gate::authorize('updateProfile', $user);

        $profileExists = OrganizerProfile::where('user_id', $user->id)->exists();

        $nib = $request->file('nib');

        if ($nib && ! $nib->isValid()) {
            $error = $nib->getError();

            Log::warning('organizer.nib.upload_failed', [
                'php_upload_error' => $error,
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ]);

            throw ValidationException::withMessages([
                'nib' => match ($error) {
                    UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'NIB ditolak oleh batas upload PHP server ('.ini_get('upload_max_filesize').'). Pilih berkas lebih kecil atau hubungi pengelola.',
                    UPLOAD_ERR_PARTIAL => 'Upload NIB terputus sebelum seluruh berkas diterima. Pilih ulang berkas dan coba kembali.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara upload pada server tidak tersedia.',
                    UPLOAD_ERR_CANT_WRITE => 'Server gagal menulis berkas sementara NIB.',
                    UPLOAD_ERR_EXTENSION => 'Upload NIB dihentikan oleh ekstensi PHP pada server.',
                    default => 'Server belum menerima berkas NIB yang valid.',
                },
            ]);
        }

        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:255'],
            'phone' => ['bail', 'required', new PhoneNumber],
            'address' => ['required', 'string', 'max:1000'],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')->where('is_active', true)],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'nib' => [
                $profileExists ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ], [
            'city_id.exists' => 'Pilih kota/kabupaten aktif dari daftar.',
            'nib.required' => 'NIB wajib diupload untuk pengajuan profil pertama kali.',
        ]);

        $newPath = null;
        $oldPaths = [];

        if ($request->hasFile('nib')) {
            $newPath = $request->file('nib')->store(
                'organizers/'.$user->id.'/documents',
                'local'
            );

            if (! $newPath) {
                throw ValidationException::withMessages([
                    'nib' => 'NIB baru gagal disimpan. Silakan coba kembali.',
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

                $city = City::whereKey($validated['city_id'])->where('is_active', true)->first();
                if (! $city) {
                    throw ValidationException::withMessages(['city_id' => 'Pilih kota/kabupaten aktif dari daftar.']);
                }

                $previous = $locked->organizerProfile;
                $cityChanged = $previous && ($previous->city_id !== null
                    ? (int) $previous->city_id !== (int) $city->id
                    : $this->cityIdentityKey($previous->city) !== $this->cityIdentityKey($city->name));
                $identityChanged = $previous && ($newPath
                    || collect(['organization_name', 'address'])
                        ->contains(fn ($field) => $validated[$field] !== $previous->$field)
                    || $cityChanged);
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
                        'city' => $city->name,
                        'city_id' => $city->id,
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
                        'document_name' => $request->file('nib')->getClientOriginalName(),
                        'file_path' => $newPath,
                    ]);
                }

                if (! $previous || $identityChanged || $locked->organizer_status === 'inactive') {
                    $locked->forceFill(['organizer_status' => 'pending'])->save();
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
                            'changed_fields' => array_values(array_unique([...array_keys($validated), 'city'])),
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

    private function cityIdentityKey(?string $city): string
    {
        return mb_strtolower(trim((string) preg_replace('/^(kota|kabupaten)\s+/i', '', (string) $city)));
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
