<?php

use App\Console\Commands\SendPaymentReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Course payment reminders. Hostinger runs `php artisan schedule:run` every
// minute; the command itself is safe to run repeatedly because each
// enrollment only gets a reminder once per cooldown interval (see
// PaymentReminderService), and withoutOverlapping prevents double runs.
Schedule::command(SendPaymentReminders::class)
    ->dailyAt('08:30')
    ->withoutOverlapping();

// Shared-hosting email queue worker. Hostinger has no persistent daemon, so a
// short-lived database worker drains the queued SendEmailJob payloads every
// minute and exits once the queue is empty. It never overlaps itself, keeps
// the job's own $tries = 3 retry behaviour authoritative, and its 60s timeout
// stays safely below the 90s retry_after configured in config/queue.php.
Schedule::command('queue:work database --stop-when-empty --tries=3 --timeout=60 --sleep=1')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
