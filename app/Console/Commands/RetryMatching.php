<?php

namespace App\Console\Commands;

use App\Models\Application;
use App\Models\MatchResult;
use App\Services\MatchingEvaluationService;
use Illuminate\Console\Command;

class RetryMatching extends Command
{
    protected $signature = 'a4:retry-matching {--limit=100 : Maksimum lamaran (1–1000)} {--after=0 : Mulai sesudah ID lamaran ini}';

    protected $description = 'Proses matching lamaran lolos screening tanpa hasil matching, aman diulang';

    public function handle(MatchingEvaluationService $matching): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT);
        $after = filter_var($this->option('after'), FILTER_VALIDATE_INT);
        if ($limit === false || $limit < 1 || $limit > 1000 || $after === false || $after < 0) {
            $this->error('Limit harus 1–1000 dan after berupa ID nonnegatif.');

            return self::FAILURE;
        }
        $applications = Application::where('status', 'submitted')->where('id', '>', $after)
            ->whereHas('screeningResult', fn ($query) => $query->where('passed', true))
            ->whereNotIn('id', MatchResult::select('application_id'))
            ->whereHas('event', fn ($query) => $query->whereNotIn('lifecycle_status', ['cancelled', 'completed']))
            ->orderBy('id')->limit($limit)->get();
        $done = $pending = 0;
        foreach ($applications as $application) {
            try {
                $matching->evaluate($application) === null ? $pending++ : $done++;
            } catch (\Throwable) {
                $pending++;
                $this->warn('Lamaran #'.$application->id.' tertunda; periksa snapshot dan kesiapan database.');
            }
        }
        $this->info("Diproses: {$done}; tertunda: {$pending}.");
        if ($applications->isNotEmpty()) {
            $this->line('ID terakhir diperiksa: '.$applications->last()->id);
        }

        return $pending ? self::FAILURE : self::SUCCESS;
    }
}
