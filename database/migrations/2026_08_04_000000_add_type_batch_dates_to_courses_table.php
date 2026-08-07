<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Only add type column if table exists and doesn't already have it
            if (! Schema::hasColumn('courses', 'type')) {
                $table->string('type')->default('course')->after('is_featured')
                    ->comment('course or service');
            }
            if (! Schema::hasColumn('courses', 'batch_start_date')) {
                $table->date('batch_start_date')->nullable()->after('type');
            }
            if (! Schema::hasColumn('courses', 'batch_end_date')) {
                $table->date('batch_end_date')->nullable()->after('batch_start_date');
            }
            if (! Schema::hasColumn('courses', 'consultation_link')) {
                $table->string('consultation_link')->nullable()->after('batch_end_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $columns = ['type', 'batch_start_date', 'batch_end_date', 'consultation_link'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('courses', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
