<?php

namespace App\Console\Commands;

use App\Services\VolunteerEmailService;
use Illuminate\Console\Command;

class RemindVolunteerAssessments extends Command
{
    protected $signature = 'a4:remind-assessments';

    protected $description = 'Antrekan satu pengingat H-1 untuk assessment yang belum pernah dimulai';

    public function handle(VolunteerEmailService $emails): int
    {
        $this->info('Pengingat baru: '.$emails->reminders().'.');

        return self::SUCCESS;
    }
}
