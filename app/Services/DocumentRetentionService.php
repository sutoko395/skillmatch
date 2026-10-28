<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\DocumentPurge;
use App\Models\Event;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentRetentionService
{
    // Persist a tombstone before deleting anything. Keep this ledger with backups,
    // independently of older SQL snapshots, so restore cannot resurrect a file.
    public function queue(ApplicationDocument $document, string $reason): void
    {
        if (DB::transactionLevel() < 1) {
            throw new \LogicException('Purge queue must share the document transaction.');
        }
        DocumentPurge::firstOrCreate(['disk' => $document->disk, 'storage_key' => $document->storage_key], ['reason' => $reason]);
        $document->forceFill(['status' => 'purged', 'purged_at' => $document->purged_at ?? now()])->save();
        app(AuditService::class)->record(null, 'document.purge_queued', $document, ['after' => ['status' => 'purged']], $reason);
    }

    public function recover(): int
    {
        $disk = Storage::disk('private');
        // File writes cannot join SQL rollback. Journals recover interrupted writes,
        // including a caller rolling back an outer transaction after store returned.
        foreach ($disk->files('a3-upload-ledger') as $key) {
            $data = json_decode($disk->get($key), true, 512, JSON_THROW_ON_ERROR);
            if (($data['disk'] ?? null) !== 'private_documents'
                || ! preg_match('#^applications/[0-9]+/[a-zA-Z0-9]+\.(pdf|jpg|jpeg|png)$#', $data['storage_key'] ?? '')) {
                throw new \RuntimeException('Jurnal upload tidak valid.');
            }
            if (ApplicationDocument::where('disk', $data['disk'])->where('storage_key', $data['storage_key'])->exists()) {
                $disk->delete($key);
            } elseif (CarbonImmutable::parse($data['created_at'])->lte(now()->subDay())) {
                DocumentPurge::firstOrCreate(['disk' => $data['disk'], 'storage_key' => $data['storage_key']], ['reason' => 'orphan_upload']);
                // Keep journal until purge ledger has successfully reached disk.
            }
        }
        foreach ($disk->files('a3-purge-ledger') as $key) {
            $data = json_decode($disk->get($key), true, 512, JSON_THROW_ON_ERROR);
            if (! in_array($data['disk'] ?? null, ['private', 'private_documents'], true)
                || ! preg_match('#^applications/[0-9]+/[a-zA-Z0-9]+\.(pdf|jpg|jpeg|png)$#', $data['storage_key'] ?? '')) {
                throw new \RuntimeException('Ledger purge tidak valid; recovery dihentikan.');
            }
            DocumentPurge::firstOrCreate(['disk' => $data['disk'], 'storage_key' => $data['storage_key']], ['reason' => 'restore_ledger']);
        }
        $count = 0;
        DocumentPurge::orderBy('id')->chunkById(100, function ($entries) use ($disk, &$count) {
            foreach ($entries as $entry) {
                if (! in_array($entry->disk, ['private', 'private_documents'], true)
                    || ! preg_match('#^applications/[0-9]+/[a-zA-Z0-9]+\.(pdf|jpg|jpeg|png)$#', $entry->storage_key)) {
                    throw new \RuntimeException('Target purge tidak valid; dokumen organisasi tidak diproses.');
                }
                $key = 'a3-purge-ledger/'.hash('sha256', $entry->disk.':'.$entry->storage_key).'.json';
                if (! $disk->put($key, json_encode(['disk' => $entry->disk, 'storage_key' => $entry->storage_key], JSON_THROW_ON_ERROR))) {
                    throw new \RuntimeException('Ledger purge gagal disimpan; file dipertahankan.');
                }
                ApplicationDocument::where('disk', $entry->disk)->where('storage_key', $entry->storage_key)
                    ->update(['status' => 'purged', 'purged_at' => now()]);
                $target = Storage::disk($entry->disk);
                if ($target->exists($entry->storage_key) && ! $target->delete($entry->storage_key)) {
                    throw new \RuntimeException('Penghapusan dokumen tertunda; jalankan recovery kembali.');
                }
                $entry->forceFill(['purged_at' => $entry->purged_at ?? now()])->save();
                $count++;
            }
        });
        ApplicationDocument::whereIn('status', ['ready', 'missing'])->orderBy('id')->chunkById(100, function ($documents) {
            foreach ($documents as $document) {
                DB::transaction(function () use ($document) {
                    $document = ApplicationDocument::whereKey($document->id)->lockForUpdate()->first();
                    if (! $document || ! in_array($document->status, ['ready', 'missing'], true)) {
                        return;
                    }
                    $target = Storage::disk($document->disk);
                    if (! $target->exists($document->storage_key)
                        || ! hash_equals($document->checksum_sha256, hash('sha256', $target->get($document->storage_key)))) {
                        $document->forceFill(['status' => 'missing'])->save();
                    } elseif ($document->status === 'missing') {
                        $document->forceFill(['status' => 'ready'])->save();
                        app(AuditService::class)->record(null, 'document.recovered', $document, ['after' => ['status' => 'ready']]);
                    }
                }, 3);
            }
        });

        return $count;
    }

    public function cleanup(): int
    {
        $draftDays = config('a3.draft_days');
        $documentDays = config('a3.document_days');
        if ($draftDays < 1 || $documentDays < 1) {
            throw new \RuntimeException('Retensi harus bernilai positif.');
        }
        Application::where(function ($query) use ($draftDays, $documentDays) {
            $query->where(fn ($q) => $q->where('status', 'draft')->where('updated_at', '<=', now()->subDays($draftDays)))
                ->orWhereHas('event', fn ($q) => $q
                    ->where(fn ($q) => $q->where('lifecycle_status', 'completed')->where('completed_at', '<=', now()->subDays($documentDays)))
                    ->orWhere(fn ($q) => $q->where('lifecycle_status', 'cancelled')->where('cancelled_at', '<=', now()->subDays($documentDays))));
        })->orderBy('id')->chunkById(100, function ($applications) use ($draftDays, $documentDays) {
            foreach ($applications as $candidate) {
                DB::transaction(function () use ($candidate, $draftDays, $documentDays) {
                    User::whereKey($candidate->volunteer_id)->lockForUpdate()->firstOrFail();
                    $event = Event::whereKey($candidate->event_id)->lockForUpdate()->firstOrFail();
                    $application = Application::whereKey($candidate->id)->lockForUpdate()->firstOrFail();
                    $draft = $application->status === 'draft' && $application->updated_at->lte(now()->subDays($draftDays));
                    $terminalAt = match ($event->lifecycle_status) {
                        'completed' => $event->completed_at,
                        'cancelled' => $event->cancelled_at,
                        default => null,
                    };
                    if (! $draft && (! $terminalAt || $terminalAt->gt(now()->subDays($documentDays)))) {
                        return;
                    }
                    foreach ($application->documents()->whereIn('status', ['ready', 'missing'])->lockForUpdate()->get() as $document) {
                        $this->queue($document, $draft ? 'abandoned_draft' : 'event_retention');
                    }
                    if ($draft) {
                        // Only draft metadata can be deleted; ledger survives FK cascade.
                        app(AuditService::class)->record(null, 'application.draft_deleted', $application, [], 'abandoned_draft');
                        $application->delete();
                    }
                }, 3);
            }
        });

        return $this->recover();
    }
}
