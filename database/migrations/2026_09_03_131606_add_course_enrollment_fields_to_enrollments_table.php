<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            // Student details — copied from the linked account at enrollment
            // time so admins can manage course sales exactly like service orders.
            $table->string('student_name')->nullable()->after('user_id');
            $table->string('student_email')->nullable()->after('student_name');
            $table->string('student_phone')->nullable()->after('student_email');
            $table->decimal('amount', 8, 2)->default(0)->after('course_id');
            $table->string('payment_status')->default('pending')->after('amount_due');
            $table->string('enrollment_status')->default('pending')->after('payment_status');
            $table->text('admin_notes')->nullable()->after('rejection_reason');

            // Enrollments may be recorded by an admin for a student without an
            // account, so the registered-user link is optional.
            $table->foreignId('user_id')->nullable()->change();
        });

        // Backfill existing rows from their linked account and legacy columns.
        DB::update('
            UPDATE enrollments
               SET student_name  = (SELECT name  FROM users WHERE users.id  = enrollments.user_id),
                   student_email = (SELECT email FROM users WHERE users.id = enrollments.user_id),
                   student_phone = (SELECT phone FROM users WHERE users.id = enrollments.user_id)
        ');

        DB::update('UPDATE enrollments SET amount = COALESCE(price_paid, 0)');

        DB::update("
            UPDATE enrollments
               SET payment_status = CASE
                       WHEN paid_at IS NOT NULL OR status = 'paid' THEN 'paid'
                       ELSE 'pending'
                   END,
                   enrollment_status = CASE status
                       WHEN 'active' THEN 'in_progress'
                       WHEN 'paid' THEN 'in_progress'
                       WHEN 'completed' THEN 'completed'
                       WHEN 'cancelled' THEN 'cancelled'
                       ELSE 'pending'
                   END
        ");

        DB::update("
            UPDATE enrollments
               SET amount_paid = CASE WHEN payment_status = 'paid' THEN amount ELSE 0 END,
                   amount_due  = amount - amount_paid
        ");
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn([
                'student_name',
                'student_email',
                'student_phone',
                'amount',
                'payment_status',
                'enrollment_status',
                'admin_notes',
            ]);
        });
    }
};
