<?php

namespace App\Console\Commands;

use App\Models\AssessmentAttempt;
use App\Services\AssessmentService;
use Illuminate\Console\Command;

class ExpireAssessments extends Command
{
    protected $signature = 'a4:expire-assessments';

    protected $description = 'Finalisasi assessment timeout dan hentikan attempt lamaran terminal';

    public function handle(AssessmentService $service): int
    {
        $failed = 0;
        AssessmentAttempt::where('status', 'in_progress')->where(function ($query) {
            $query->where('expires_at', '<=', now())
                ->orWhereHas('application', fn ($q) => $q->where('status', '!=', 'assessment_pending'))
                ->orWhereHas('application.event', fn ($q) => $q->whereIn('lifecycle_status', ['cancelled', 'completed']))
                ->orWhereHas('application.volunteer', fn ($q) => $q->where('is_active', false));
        })->with('application')->chunkById(100, function ($attempts) use ($service, &$failed) {
            foreach ($attempts as $attempt) {
                try {
                    $service->expire($attempt->application);
                } catch (\Throwable) {
                    $failed++;
                    $this->warn('Attempt #'.$attempt->id.' tertunda; jalankan ulang setelah masalah terselesaikan.');
                }
            }
        });
        $this->info('Finalisasi selesai; tertunda: '.$failed.'.');

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
