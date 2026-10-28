<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Event;
use App\Models\OrganizerDocument;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DocumentStorageService
{
    public const MAX_BYTES = 5242880; // 5 MB

    public const MAX_FILES_PER_APPLICATION = 5;

    /** Shared A1 validator. A1 persists its own schema; this does not claim ready. */
    public function validateOrganizationUpload(UploadedFile $file, User $owner, User $actor): array
    {
        Gate::forUser($actor)->authorize('create', [OrganizerDocument::class, $owner]);
        $this->validateUpload($file, 'supporting');

        return ['original_name' => mb_substr(basename(str_replace('\\', '/', $file->getClientOriginalName())), 0, 255),
            'mime_type' => $file->getMimeType(), 'size_bytes' => $file->getSize(),
            'checksum_sha256' => hash_file('sha256', $file->getRealPath())];
    }

    public function downloadOrganization(OrganizerDocument $document, User $actor): Response
    {
        Gate::forUser($actor)->authorize('download', $document);
        // Legacy A1 schema has file_path only. Never fall back to the public disk.
        $disk = Storage::disk('private_documents');
        abort_unless($document->file_path && $disk->exists($document->file_path), 404);
        DB::transaction(fn () => app(AuditService::class)->record($actor, 'organization_document.downloaded', $document));

        return $disk->download($document->file_path, $document->document_name,
            ['Content-Type' => $disk->mimeType($document->file_path), 'X-Content-Type-Options' => 'nosniff']);
    }

    /**
     * Store private document with strict validation, random key, sha256 checksum, and compensation.
     *
     * @param  array{type: string, application?: Application, document_type?: string}  $context
     */
    public function store(UploadedFile $file, array $context, User $actor): ApplicationDocument
    {
        $type = $context['type'] ?? 'application';

        if ($type !== 'application') {
            throw new \InvalidArgumentException("Context type '{$type}' belum didukung.");
        }

        /** @var Application $application */
        $application = $context['application'] ?? null;
        if (! $application) {
            throw new \InvalidArgumentException('Application context wajib diisi.');
        }

        // Authorization check for application document upload
        if (! Gate::forUser($actor)->allows('create', [ApplicationDocument::class, $application])) {
            throw new AuthorizationException('Anda tidak berhak mengunggah dokumen pada lamaran ini.');
        }

        $documentType = $context['document_type'] ?? 'cv';
        if (! in_array($documentType, ['cv', 'supporting'], true)) {
            throw ValidationException::withMessages([
                'document' => 'Tipe dokumen tidak valid.',
            ]);
        }

        $this->validateUpload($file, $documentType);

        return DB::transaction(function () use ($application, $actor, $documentType, $file) {
            User::whereKey($application->volunteer_id)->lockForUpdate()->firstOrFail();
            Event::whereKey($application->event_id)->lockForUpdate()->firstOrFail();
            $application = Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor->fresh())->authorize('create', [ApplicationDocument::class, $application]);
            // Application lock serializes upload, submit, replacement and cleanup.
            $existingCount = $application->documents()
                ->where('status', 'ready')
                ->count();

            // If uploading CV and an existing CV is already present on this draft, allow replacement
            $existingCv = null;
            if ($documentType === 'cv') {
                $existingCv = $application->documents()
                    ->where('document_type', 'cv')
                    ->where('status', 'ready')
                    ->first();
            }

            if (! $existingCv && $existingCount >= self::MAX_FILES_PER_APPLICATION) {
                throw ValidationException::withMessages([
                    'document' => 'Maksimal '.self::MAX_FILES_PER_APPLICATION.' file per lamaran.',
                ]);
            }

            $disk = 'private_documents';
            $extension = strtolower($file->getClientOriginalExtension());
            $storageKey = 'applications/'.$application->id.'/'.Str::random(40).'.'.$extension;
            $sizeBytes = $file->getSize();
            $mimeType = $file->getMimeType();
            $checksum = hash_file('sha256', $file->getRealPath());

            $journal = 'a3-upload-ledger/'.hash('sha256', $storageKey).'.json';
            if (! Storage::disk('private')->put($journal, json_encode(['disk' => $disk, 'storage_key' => $storageKey, 'created_at' => now()->toISOString()], JSON_THROW_ON_ERROR))) {
                throw new \RuntimeException('Jurnal penyimpanan dokumen gagal disimpan.');
            }

            // Save file physically to private disk
            $saved = Storage::disk($disk)->put($storageKey, file_get_contents($file->getRealPath()));
            if (! $saved) {
                throw new \RuntimeException('Gagal menyimpan file ke disk privat.');
            }

            // Transactional save / DB compensation
            try {
                $document = ApplicationDocument::create([
                    'application_id' => $application->id,
                    'uploader_id' => $actor->id,
                    'document_type' => $documentType,
                    'disk' => $disk,
                    'storage_key' => $storageKey,
                    'original_name' => mb_substr(basename(str_replace('\\', '/', $file->getClientOriginalName())), 0, 255),
                    'mime_type' => $mimeType,
                    'size_bytes' => $sizeBytes,
                    'checksum_sha256' => $checksum,
                    'status' => 'ready',
                    'ready_at' => now(),
                ]);
                if ($existingCv) {
                    app(DocumentRetentionService::class)->queue($existingCv, 'cv_replaced');
                }
                $application->touch();
                app(AuditService::class)->record($actor, 'document.stored', $document);

                return $document;
            } catch (\Throwable $e) {
                // Compensation: delete physical file if database insertion fails
                Storage::disk($disk)->delete($storageKey);
                throw $e;
            }
        });
    }

    /**
     * Delete private document.
     */
    public function delete(ApplicationDocument $document, User $actor): void
    {
        DB::transaction(function () use ($document, $actor) {
            $application = $document->application;
            User::whereKey($application->volunteer_id)->lockForUpdate()->firstOrFail();
            Event::whereKey($application->event_id)->lockForUpdate()->firstOrFail();
            Application::whereKey($application->id)->lockForUpdate()->firstOrFail();
            $document = ApplicationDocument::whereKey($document->id)->lockForUpdate()->firstOrFail();
            $document->unsetRelation('application');
            Gate::forUser($actor->fresh())->authorize('delete', $document);
            app(DocumentRetentionService::class)->queue($document, 'volunteer_deleted');
            app(AuditService::class)->record($actor, 'document.deleted', $document);
            DB::afterCommit(fn () => app(DocumentRetentionService::class)->recover());
        }, 3);
    }

    /**
     * Authorized download of private document as attachment.
     */
    public function download(ApplicationDocument $document, User $actor): Response
    {
        Gate::forUser($actor)->authorize('download', $document);

        if ($document->status !== 'ready') {
            abort(404, 'Dokumen tidak tersedia atau telah dihapus.');
        }

        if (! Storage::disk($document->disk)->exists($document->storage_key)) {
            $document->forceFill(['status' => 'missing'])->save();
            abort(404, 'File fisik dokumen tidak ditemukan.');
        }

        DB::transaction(fn () => app(AuditService::class)->record($actor, 'document.downloaded', $document));

        return Storage::disk($document->disk)->download(
            $document->storage_key,
            $document->original_name,
            ['Content-Type' => $document->mime_type, 'X-Content-Type-Options' => 'nosniff']
        );
    }

    /**
     * Validate file size, extension, and content-based MIME type.
     */
    protected function validateUpload(UploadedFile $file, string $documentType): void
    {
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                'document' => 'File yang diunggah tidak valid atau rusak.',
            ]);
        }

        if ($file->getSize() < 1 || $file->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'document' => 'Ukuran file tidak boleh melebihi 5 MB.',
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType();

        if ($documentType === 'cv') {
            if ($extension !== 'pdf' || $mime !== 'application/pdf') {
                throw ValidationException::withMessages([
                    'document' => 'Dokumen CV wajib berupa file PDF dengan konten yang valid.',
                ]);
            }
        } elseif ($documentType === 'supporting') {
            $allowed = [
                'pdf' => ['application/pdf'],
                'jpg' => ['image/jpeg'],
                'jpeg' => ['image/jpeg'],
                'png' => ['image/png'],
            ];

            if (! isset($allowed[$extension]) || ! in_array($mime, $allowed[$extension], true)) {
                throw ValidationException::withMessages([
                    'document' => 'Dokumen pendukung wajib berupa file PDF, JPG, atau PNG dengan konten yang valid.',
                ]);
            }
        }
    }
}
