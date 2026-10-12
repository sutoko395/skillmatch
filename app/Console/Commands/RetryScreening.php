<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Services\ScreeningService;
use Illuminate\Console\Command;

class RetryScreening extends Command
{
    protected $signature = 'a4:retry-screening {--limit=100 : Maksimum lamaran per proses (1–1000)}';

    protected $description = 'Evaluasi lamaran submitted tanpa hasil screening, aman diulang';

    public function handle(ScreeningService $screening): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        if ($limit === false || $limit < 1 || $limit > 1000) {
            $this->error('Limit harus 1–1000.');

            return self::FAILURE;
        }
        $done = $pending = 0;
        $applications = Application::where('status', 'submitted')->whereDoesntHave('screeningResult')
            ->whereHas('event', fn ($query) => $query->whereNotIn('lifecycle_status', ['cancelled', 'completed']))
            ->orderBy('id')->limit($limit)->get();
        foreach ($applications as $application) {
            try {
                $screening->evaluate($application) === null ? $pending++ : $done++;
            } catch (\Throwable) {
                $pending++;
                // Operational identifier only; no profile/document contents or database errors.
                $this->warn('Lamaran #'.$application->id.' belum dapat dievaluasi; periksa snapshot atau kesiapan database.');
            }
        }
        $this->info("Dievaluasi: {$done}; tertunda: {$pending}.");

        return $pending ? self::FAILURE : self::SUCCESS;
    }
}
