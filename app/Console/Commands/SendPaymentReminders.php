<?php

namespace App\Console\Commands;

use App\Services\PaymentReminderService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('payment-reminders:send')]
#[Description('Queue payment reminder emails for overdue course enrollments')]
class SendPaymentReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(PaymentReminderService $reminders): int
    {
        $queued = $reminders->sendDueReminders();

        $this->info("Payment reminders queued: {$queued}");

        return self::SUCCESS;
    }
}
