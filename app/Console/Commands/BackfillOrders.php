<?php

namespace App\Console\Commands;

use App\Models\DigitalOrder;
use App\Models\Enrollment;
use App\Models\ServiceOrder;
use App\Services\OrderMaterializer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Historical data migration only: copies the legacy sale tables
 * (enrollments, digital_orders, service_orders) into the unified
 * orders → order_items → payments structure.
 *
 * The row mapping itself lives in OrderMaterializer; this command only
 * iterates the legacy rows, merges the per-row results into the console
 * report, and never writes to the legacy tables.
 */
#[Signature('orders:backfill')]
#[Description('Backfill unified orders, order items and payments from the legacy sale tables')]
class BackfillOrders extends Command
{
    protected const CHUNK_SIZE = 200;

    /** @var array{orders: int, items: int, payments: int, skipped: int, warnings: int, errors: int} */
    protected array $stats = [
        'orders' => 0,
        'items' => 0,
        'payments' => 0,
        'skipped' => 0,
        'warnings' => 0,
        'errors' => 0,
    ];

    /** @var list<string> */
    protected array $errorMessages = [];

    /** @var list<string> */
    protected array $warningMessages = [];

    public function handle(OrderMaterializer $materializer): int
    {
        $this->info('orders:backfill — historical mapping into unified checkout tables');
        $this->newLine();

        $this->backfillEnrollments($materializer);
        $this->backfillDigitalOrders($materializer);
        $this->backfillServiceOrders($materializer);

        $this->printSummary();

        return self::SUCCESS;
    }

    protected function backfillEnrollments(OrderMaterializer $materializer): void
    {
        $total = Enrollment::count();

        $this->info(sprintf('Backfilling enrollments (%d row%s)', $total, $total === 1 ? '' : 's'));
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Enrollment::query()->orderBy('id')->chunkById(self::CHUNK_SIZE, function ($rows) use ($bar, $materializer): void {
            foreach ($rows as $enrollment) {
                $this->processRow($materializer, $enrollment);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
    }

    protected function backfillDigitalOrders(OrderMaterializer $materializer): void
    {
        $total = DigitalOrder::count();

        $this->info(sprintf('Backfilling digital orders (%d row%s)', $total, $total === 1 ? '' : 's'));
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        DigitalOrder::query()->orderBy('id')->chunkById(self::CHUNK_SIZE, function ($rows) use ($bar, $materializer): void {
            foreach ($rows as $order) {
                $this->processRow($materializer, $order);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
    }

    protected function backfillServiceOrders(OrderMaterializer $materializer): void
    {
        $total = ServiceOrder::count();

        $this->info(sprintf('Backfilling service orders (%d row%s)', $total, $total === 1 ? '' : 's'));
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        ServiceOrder::query()->orderBy('id')->chunkById(self::CHUNK_SIZE, function ($rows) use ($bar, $materializer): void {
            foreach ($rows as $order) {
                $this->processRow($materializer, $order);
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
    }

    protected function processRow(OrderMaterializer $materializer, Model $row): void
    {
        try {
            $result = $materializer->materialize($row);

            $this->stats['orders'] += $result['orders'];
            $this->stats['items'] += $result['items'];
            $this->stats['payments'] += $result['payments'];
            $this->stats['skipped'] += $result['skipped'];
            $this->stats['warnings'] += count($result['warnings']);

            foreach ($result['warnings'] as $warning) {
                $this->warningMessages[] = $warning;
            }
        } catch (Throwable $exception) {
            $this->recordFailure($row, $exception);
        }
    }

    protected function recordFailure(Model $row, Throwable $exception): void
    {
        $this->stats['errors']++;

        $source = match (true) {
            $row instanceof Enrollment => 'enrollment',
            $row instanceof DigitalOrder => 'digital_order',
            $row instanceof ServiceOrder => 'service_order',
            default => $row::class,
        };

        $message = sprintf('Failed to backfill %s #%d: %s', $source, $row->getKey(), $exception->getMessage());
        $this->errorMessages[] = $message;

        Log::error('orders:backfill failed for a legacy row.', [
            'legacy_source' => $source,
            'legacy_id' => $row->getKey(),
            'exception' => $exception->getMessage(),
        ]);
    }

    protected function printSummary(): void
    {
        $this->info('Backfill complete.');
        $this->newLine();

        $this->table(['Metric', 'Count'], [
            ['Orders created', $this->stats['orders']],
            ['Order items created', $this->stats['items']],
            ['Payments created', $this->stats['payments']],
            ['Rows skipped (already backfilled)', $this->stats['skipped']],
            ['Rows with fallback snapshots', $this->stats['warnings']],
            ['Rows failed', $this->stats['errors']],
        ]);

        if ($this->stats['errors'] > 0 || $this->stats['warnings'] > 0) {
            $this->newLine();

            foreach (array_slice($this->warningMessages, 0, 10) as $warning) {
                $this->warn('WARNING: '.$warning);
            }

            foreach (array_slice($this->errorMessages, 0, 10) as $error) {
                $this->error('ERROR: '.$error);
            }
        }
    }
}
