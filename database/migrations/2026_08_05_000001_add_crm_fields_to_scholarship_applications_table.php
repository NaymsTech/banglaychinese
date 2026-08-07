<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            // If status column doesn't exist yet, create it
            if (!Schema::hasColumn('scholarship_applications', 'status')) {
                $table->string('status')->default('new');
            }

            // CRM management fields
            if (!Schema::hasColumn('scholarship_applications', 'admin_notes')) {
                $table->text('admin_notes')->nullable();
            }

            if (!Schema::hasColumn('scholarship_applications', 'follow_up_date')) {
                $table->date('follow_up_date')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->dropColumn(['admin_notes', 'follow_up_date']);
        });
    }
};
