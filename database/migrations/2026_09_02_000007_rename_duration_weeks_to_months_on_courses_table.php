<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->renameColumn('duration_weeks', 'duration_months');
        });

        // Convert the stored week counts to months (≈ 4 weeks per month).
        // Existing values (8/12/16/20/24 weeks) become 2/3/4/5/6 months.
        foreach (DB::table('courses')->whereNotNull('duration_months')->pluck('duration_months', 'id') as $id => $weeks) {
            DB::table('courses')->where('id', $id)->update([
                'duration_months' => max(1, (int) round((int) $weeks / 4)),
            ]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('courses')->whereNotNull('duration_months')->pluck('duration_months', 'id') as $id => $months) {
            DB::table('courses')->where('id', $id)->update([
                'duration_months' => (int) $months * 4,
            ]);
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->renameColumn('duration_months', 'duration_weeks');
        });
    }
};
