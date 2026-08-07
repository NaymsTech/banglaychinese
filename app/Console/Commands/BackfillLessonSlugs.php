<?php

namespace App\Console\Commands;

use App\Models\Lesson;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillLessonSlugs extends Command
{
    protected $signature = 'lessons:backfill-slugs';

    protected $description = 'Generate slugs for lessons that have NULL slugs. Ensures uniqueness within each course.';

    public function handle(): int
    {
        $lessons = Lesson::with('module.course')
            ->whereNull('slug')
            ->get();

        if ($lessons->isEmpty()) {
            $this->info('No lessons with NULL slugs found.');

            return self::SUCCESS;
        }

        $this->info("Found {$lessons->count()} lesson(s) with NULL slugs.");

        $updated = 0;

        foreach ($lessons as $lesson) {
            $courseId = $lesson->module?->course?->id;

            if (! $courseId) {
                $this->warn("Skipping lesson ID {$lesson->id} — no course found via module relation.");

                continue;
            }

            $baseSlug = Str::slug($lesson->title);
            $slug = $baseSlug;
            $counter = 1;

            // Ensure uniqueness within the same course
            while (Lesson::whereHas('module', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })->where('slug', $slug)->where('id', '!=', $lesson->id)->exists()) {
                $slug = $baseSlug.'-'.$counter;
                $counter++;
            }

            $lesson->update(['slug' => $slug]);

            $this->line("  Lesson ID {$lesson->id} — \"{$lesson->title}\" => slug: \"{$slug}\"");
            $updated++;
        }

        $this->info("{$updated} lesson slug(s) backfilled successfully.");

        return self::SUCCESS;
    }
}
