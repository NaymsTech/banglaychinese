<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class ScheduleConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_database_email_queue_worker_is_scheduled_every_minute(): void
    {
        $output = new BufferedOutput;

        $exitCode = Artisan::call('schedule:list', [], $output);

        $display = $output->fetch();

        $this->assertSame(0, $exitCode);

        // The shared-hosting email worker and the existing payment reminder
        // command are both registered through routes/console.php.
        $this->assertStringContainsString('queue:work database', $display);
        $this->assertStringContainsString('--stop-when-empty', $display);
        $this->assertStringContainsString('payment-reminders:send', $display);
    }
}
