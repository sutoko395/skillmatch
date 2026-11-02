<?php

namespace App\Console\Commands;

use App\Models\NotificationOutbox;
use App\Services\VolunteerEmailDelivery;
use Illuminate\Console\Command;

class SendVolunteerEmails extends Command
{
    protected $signature = 'a4:send-volunteer-emails {--limit=100} {--retry-failed} {--retry-uncertain= : ID email yang sudah diperiksa sebelum dicoba ulang}';

    protected $description = 'Proses antrean empat email volunteer; dedupe, klaim worker dan retry terkontrol';

    public function handle(VolunteerEmailDelivery $delivery): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        $uncertain = $this->option('retry-uncertain');
        if ($limit === false || $limit < 1 || $limit > 1000
            || ($uncertain !== null && (! ctype_digit((string) $uncertain) || (int) $uncertain < 1))) {
            $this->error('Limit harus 1–1000 dan ID retry harus positif.');

            return self::FAILURE;
        }
        NotificationOutbox::where('channel', 'mail')->where('status', 'processing')->where('claimed_at', '<=', now()->subMinutes(15))
            ->update(['status' => 'uncertain', 'claim_token' => null, 'error_code' => 'worker_outcome_uncertain']);
        if ($this->option('retry-failed')) {
            NotificationOutbox::where('channel', 'mail')->where('status', 'failed')
                ->update(['status' => 'pending', 'attempts' => 0, 'available_at' => now()]);
        }
        if ($uncertain !== null) {
            $this->warn('Retry SMTP yang hasilnya belum pasti dapat mengirim ulang email yang sebelumnya sudah diterima.');
            NotificationOutbox::whereKey((int) $uncertain)->where('channel', 'mail')->where('status', 'uncertain')
                ->update(['status' => 'pending', 'attempts' => 0, 'available_at' => now()]);
        }
        $counts = [];
        $ids = NotificationOutbox::where('channel', 'mail')->whereIn('status', ['pending', 'failed'])
            ->where('available_at', '<=', now())->where('attempts', '<', 5)->orderBy('id')->limit($limit)->pluck('id');
        foreach ($ids as $id) {
            try {
                $result = $delivery->deliver($id);
                $counts[$result] = ($counts[$result] ?? 0) + 1;
                if ($result === 'uncertain') {
                    $this->warn('Email #'.$id.' belum pasti terkirim; periksa sebelum memakai --retry-uncertain='.$id.'.');
                }
            } catch (\Throwable) {
                $counts['uncertain'] = ($counts['uncertain'] ?? 0) + 1;
                $this->warn('Email #'.$id.' belum terkonfirmasi; detail sensitif tidak dicetak.');
            }
        }
        $this->info('Dikirim: '.($counts['sent'] ?? 0).'; dilewati: '.($counts['skipped'] ?? 0)
            .'; gagal: '.($counts['failed'] ?? 0).'; perlu diperiksa: '.($counts['uncertain'] ?? 0).'.');

        return ($counts['failed'] ?? 0) + ($counts['uncertain'] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
