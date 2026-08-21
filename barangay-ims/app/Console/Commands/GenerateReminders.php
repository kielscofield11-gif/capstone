<?php

namespace App\Console\Commands;

use App\Services\ReminderService;
use Illuminate\Console\Command;

class GenerateReminders extends Command
{
    protected $signature = 'reminders:generate';
    protected $description = 'Generate idempotent in-app operational reminders';

    public function handle(ReminderService $reminders): int
    {
        $count = $reminders->generate();
        $this->info("Generated {$count} new reminder notification(s).");
        return self::SUCCESS;
    }
}
