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
        Schema::table('email_logs', function (Blueprint $table) {
            $table->timestamp('queued_at')->nullable()->after('status');
            $table->timestamp('sending_at')->nullable()->after('queued_at');
            $table->timestamp('failed_at')->nullable()->after('sent_at');
            $table->unsignedInteger('attempt_count')->default(0)->after('sent_at');
            $table->string('message_id')->nullable()->index()->after('attempt_count');
            $table->string('from_address')->nullable()->after('subject');
            $table->string('from_name')->nullable()->after('from_address');
            $table->longText('body')->nullable()->after('subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropColumn([
                'queued_at',
                'sending_at',
                'failed_at',
                'attempt_count',
                'message_id',
                'from_address',
                'from_name',
                'body',
            ]);
        });
    }
};
