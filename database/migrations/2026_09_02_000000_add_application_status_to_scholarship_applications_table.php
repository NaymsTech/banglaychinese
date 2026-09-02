<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Split the overloaded `status` column on scholarship_applications:
     *
     *  - `status` becomes the CRM lead pipeline only:
     *    new → contacted → consultation_scheduled → application_started → converted → closed
     *  - `application_status` (nullable) carries the Scholarships review outcome:
     *    approved / rejected / null (= pending, not yet reviewed)
     *
     * Legacy rows are remapped so no existing meaning is lost.
     */
    public function up(): void
    {
        if (! Schema::hasTable('scholarship_applications')) {
            return;
        }

        if (! Schema::hasColumn('scholarship_applications', 'application_status')) {
            Schema::table('scholarship_applications', function (Blueprint $table) {
                $table->string('application_status')->nullable()->after('status');
            });
        }

        $legacy = DB::table('scholarship_applications')
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->get(['id', 'status']);

        foreach ($legacy as $row) {
            DB::table('scholarship_applications')->where('id', $row->id)->update([
                // Preserve the decision that was previously stored in `status`.
                'application_status' => $row->status === 'pending' ? null : $row->status,
                // Undecided applications re-enter the pipeline as fresh leads;
                // decided ones move to a terminal pipeline state (outcome kept above).
                'status' => $row->status === 'pending' ? 'new' : 'closed',
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('scholarship_applications')) {
            return;
        }

        if (Schema::hasColumn('scholarship_applications', 'application_status')) {
            // Best-effort reversal for the rows this migration moved to `closed`.
            DB::table('scholarship_applications')->where('application_status', 'approved')
                ->update(['status' => 'approved', 'application_status' => null]);
            DB::table('scholarship_applications')->where('application_status', 'rejected')
                ->update(['status' => 'rejected', 'application_status' => null]);

            Schema::table('scholarship_applications', function (Blueprint $table) {
                $table->dropColumn('application_status');
            });
        }
    }
};
