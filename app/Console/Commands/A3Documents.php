<?php

namespace App\Console\Commands;

use App\Services\DocumentRetentionService;
use Illuminate\Console\Command;

class A3Documents extends Command
{
    protected $signature = 'a3:documents {action : cleanup or recover} {--execute : Apply changes; otherwise only show policy}';

    protected $description = 'Retensi dokumen aplikasi dan penerapan kembali purge ledger setelah restore';

    public function handle(DocumentRetentionService $service): int
    {
        if (! in_array($this->argument('action'), ['cleanup', 'recover'], true)) {
            $this->error('Gunakan cleanup atau recover.');

            return self::FAILURE;
        }
        if (! $this->option('execute')) {
            $this->info('Tidak ada perubahan. Baseline demo: draft '.config('a3.draft_days').' hari; dokumen '.config('a3.document_days').' hari setelah completed/cancelled. Gunakan --execute setelah backup dan pemeriksaan target.');

            return self::SUCCESS;
        }
        if ($this->argument('action') === 'cleanup' && ! app()->environment(['local', 'testing']) && ! config('a3.retention_enabled')) {
            $this->error('Tetapkan kebijakan retensi publik lalu aktifkan A3_RETENTION_ENABLED sebelum cleanup.');

            return self::FAILURE;
        }
        try {
            $count = $this->argument('action') === 'cleanup' ? $service->cleanup() : $service->recover();
            $this->info('Purge ledger diproses: '.$count.'.');

            return self::SUCCESS;
        } catch (\Throwable) {
            $this->error('Pemrosesan dokumen tertunda. Periksa izin storage/database dan jalankan recover kembali; detail privat tidak dicetak.');

            return self::FAILURE;
        }
    }
}
