<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('scholarship_applications', 'budget')) {
                $table->string('budget')->nullable()->after('target_intake');
            }
            if (!Schema::hasColumn('scholarship_applications', 'preferred_consultation_time')) {
                $table->string('preferred_consultation_time')->nullable()->after('budget');
            }
        });
    }

    public function down(): void
    {
        Schema::table('scholarship_applications', function (Blueprint $table) {
            $table->dropColumn(['budget', 'preferred_consultation_time']);
        });
    }
};
