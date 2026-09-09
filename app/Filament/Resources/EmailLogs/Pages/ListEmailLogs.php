<?php

namespace App\Filament\Resources\EmailLogs\Pages;

use App\Filament\Resources\EmailLogs\EmailLogResource;
use App\Services\EmergencyEmailQueueProcessor;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;

class ListEmailLogs extends ListRecords
{
    protected static string $resource = EmailLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('processPendingEmails')
                ->label('Process Pending Emails')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->tooltip('Emergency fallback — manually processes queued email jobs when the normal queue worker is unavailable.')
                ->requiresConfirmation()
                ->modalHeading('Process pending email jobs?')
                ->modalDescription('This manually processes a limited number of queued email jobs. Use this only when the normal queue worker is unavailable — it is not a replacement for the worker, and jobs over the limit stay queued for it.')
                ->modalSubmitActionLabel('Process now')
                ->action(function (): void {
                    $result = app(EmergencyEmailQueueProcessor::class)->drainPendingEmails();

                    $attempted = $result['attempted'];

                    if ($attempted === 0) {
                        Notification::make()
                            ->info()
                            ->title('No pending email jobs')
                            ->body('The queue is empty or holds no jobs ready to run yet.')
                            ->send();

                        return;
                    }

                    $jobLabel = Str::plural('job', $attempted);
                    $failed = $result['failed'];
                    $released = $result['released'];

                    $body = $failed === 0 && $released === 0
                        ? "All {$attempted} pending email {$jobLabel} were delivered through the normal queue pipeline."
                        : "{$result['succeeded']} delivered, {$failed} failed, and {$released} released for the queue worker to retry.";

                    $notification = Notification::make()
                        ->title("Processed {$attempted} pending email {$jobLabel}")
                        ->body($body);

                    if ($failed > 0 || $released > 0) {
                        $notification->warning();
                    } else {
                        $notification->success();
                    }

                    $notification->send();
                }),
        ];
    }
}
