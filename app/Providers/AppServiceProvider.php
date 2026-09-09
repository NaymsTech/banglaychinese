<?php

namespace App\Providers;

use App\Notifications\Channels\EmailServiceChannel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auth emails are delivered through the queued EmailService instead of
        // the framework's mail channel, so they share provider routing,
        // EmailLog tracking and credential handling with every other email.
        //
        // Registered → verification email and Verified → welcome email
        // listeners are auto-discovered from app/Listeners and registered by
        // the framework, so they must not be registered again here.
        Notification::extend('email-service', fn (): EmailServiceChannel => new EmailServiceChannel);
    }
}
