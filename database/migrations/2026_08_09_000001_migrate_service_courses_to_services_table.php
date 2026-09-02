<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Copy existing Study-in-China service packages out of the courses table
     * into the dedicated services table, then remove them from courses so that
     * the courses table only holds education products.
     */
    public function up(): void
    {
        if (! Schema::hasTable('courses') || ! Schema::hasTable('services')) {
            return;
        }

        if (! Schema::hasColumn('courses', 'type')) {
            return;
        }

        $serviceCourses = DB::table('courses')->where('type', 'service')->orderBy('price')->get();

        foreach ($serviceCourses as $index => $course) {
            // Idempotent backfill keyed on slug.
            $exists = DB::table('services')->where('slug', $course->slug)->exists();

            if ($exists) {
                continue;
            }

            DB::table('services')->insert([
                'name'              => $course->title,
                'slug'              => $course->slug,
                'short_description' => null,
                'description'       => $course->description,
                'features'          => null,
                'price'             => $course->price,
                'duration'          => null,
                'status'            => (bool) $course->is_published,
                'sort_order'        => $index + 1,
                'created_at'        => $course->created_at,
                'updated_at'        => $course->updated_at,
            ]);
        }

        // Only remove course rows that are not referenced by enrollments.
        $enrolledIds = DB::table('enrollments')
            ->whereIn('course_id', $serviceCourses->pluck('id'))
            ->pluck('course_id');

        $toDelete = $serviceCourses->reject(fn ($course) => $enrolledIds->contains($course->id))->pluck('id');

        if ($toDelete->isNotEmpty()) {
            DB::table('courses')->whereIn('id', $toDelete)->delete();
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('courses') || ! Schema::hasTable('services')) {
            return;
        }

        $services = DB::table('services')->get();

        foreach ($services as $service) {
            $exists = DB::table('courses')->where('slug', $service->slug)->exists();

            if ($exists) {
                continue;
            }

            DB::table('courses')->insert([
                'title'             => $service->name,
                'slug'              => $service->slug,
                'description'       => $service->description,
                'hsk_level'         => null,
                'price'             => $service->price,
                'thumbnail'         => null,
                'is_published'      => (bool) $service->status,
                'is_featured'       => false,
                'type'              => 'service',
                'duration_weeks'    => null,
                'batch_start_date'  => null,
                'batch_end_date'    => null,
                'consultation_link' => null,
                'created_at'        => $service->created_at,
                'updated_at'        => $service->updated_at,
            ]);
        }
    }
};
