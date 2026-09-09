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
        // Queued and in-flight emails have not been sent yet, so their log
        // rows must be able to exist without a sent_at timestamp.
        Schema::table('email_logs', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable(false)->change();
        });
    }
};
