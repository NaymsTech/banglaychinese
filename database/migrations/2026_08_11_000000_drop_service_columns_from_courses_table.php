<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Courses now only represent Chinese-language learning products. The
     * Study-in-China service concept moved to the dedicated `services` table,
     * so the `type` and service-only `consultation_link` columns are removed.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('courses', 'consultation_link')) {
                $table->dropColumn('consultation_link');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'type')) {
                $table->string('type')->default('course')->after('is_featured')
                    ->comment('course only — services live in the services table');
            }
            if (! Schema::hasColumn('courses', 'consultation_link')) {
                $table->string('consultation_link')->nullable()->after('batch_end_date');
            }
        });
    }
};
