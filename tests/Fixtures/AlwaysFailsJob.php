<?php

namespace Tests\Fixtures;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;

/**
 * Queue job used only by tests to exercise the emergency processor's
 * handling of released and permanently-failed jobs without touching the
 * real email delivery path.
 */
class AlwaysFailsJob implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    public function handle(): void
    {
        throw new RuntimeException('Simulated delivery failure.');
    }
}
