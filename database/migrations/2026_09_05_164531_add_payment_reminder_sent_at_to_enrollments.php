<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Timestamp of the last payment-reminder email queued for an
        // enrollment. Reminders are only allowed again after the cooldown
        // period, which makes every manual and scheduled run idempotent.
        Schema::table('enrollments', function (Blueprint $table) {
            $table->timestamp('payment_reminder_sent_at')->nullable()->after('confirmation_email_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('payment_reminder_sent_at');
        });
    }
};
