<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DocumentStorageService
{
    public const MAX_BYTES = 5242880; // 5 MB

    public const MAX_FILES_PER_APPLICATION = 5;

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

        // Check document count limit
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
                'document' => 'Maksimal ' . self::MAX_FILES_PER_APPLICATION . ' file per lamaran.',
            ]);
        }

        $disk = 'private';
        $extension = strtolower($file->getClientOriginalExtension());
        $storageKey = 'applications/' . $application->id . '/' . Str::random(40) . '.' . $extension;
        $sizeBytes = $file->getSize();
        $mimeType = $file->getMimeType();
        $checksum = hash_file('sha256', $file->getRealPath());

        // Save file physically to private disk
        $saved = Storage::disk($disk)->put($storageKey, file_get_contents($file->getRealPath()));
        if (! $saved) {
            throw new \RuntimeException('Gagal menyimpan file ke disk privat.');
        }

        // Transactional save / DB compensation
        try {
            if ($existingCv) {
                // Remove old physical file and delete old record
                Storage::disk($existingCv->disk)->delete($existingCv->storage_key);
                $existingCv->delete();
            }

            return ApplicationDocument::create([
                'application_id' => $application->id,
                'uploader_id' => $actor->id,
                'document_type' => $documentType,
                'disk' => $disk,
                'storage_key' => $storageKey,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'size_bytes' => $sizeBytes,
                'checksum_sha256' => $checksum,
                'status' => 'ready',
                'ready_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Compensation: delete physical file if database insertion fails
            Storage::disk($disk)->delete($storageKey);
            throw $e;
        }
    }

    /**
     * Delete private document.
     */
    public function delete(ApplicationDocument $document, User $actor): void
    {
        Gate::forUser($actor)->authorize('delete', $document);

        Storage::disk($document->disk)->delete($document->storage_key);
        $document->delete();
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
            abort(404, 'File fisik dokumen tidak ditemukan.');
        }

        return Storage::disk($document->disk)->download(
            $document->storage_key,
            $document->original_name
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

        if ($file->getSize() > self::MAX_BYTES) {
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
