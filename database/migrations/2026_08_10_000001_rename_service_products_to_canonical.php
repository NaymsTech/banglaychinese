<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Canonical product mapping for the three Study in China services.
     * The key is the OLD slug (current value in the DB / legacy WordPress export);
     * the value is the NEW canonical slug.
     */
    protected function mapping(): array
    {
        return [
            'study-in-china-application-guide' => [
                'slug'          => 'guided-application',
                'name'          => 'Guided Application',
                'short_description' => 'For students who want expert direction but complete applications themselves.',
                'cta_label'     => 'Get Eligibility Review',
            ],
            'study-in-china-complete-support' => [
                'slug'          => 'full-application-service',
                'name'          => 'Full Application Service',
                'short_description' => 'Complete admission support from application to visa.',
                'cta_label'     => 'Book Consultation',
            ],
            'complete-china-success' => [
                'slug'          => 'elite-success-program',
                'name'          => 'Elite Success Program',
                'short_description' => 'Premium 12-month mentorship including language training and career guidance.',
                'cta_label'     => 'Book Consultation',
            ],
        ];
    }

    public function up(): void
    {
        if (! Schema::hasTable('services')) {
            return;
        }

        foreach ($this->mapping() as $oldSlug => $data) {
            $row = DB::table('services')->where('slug', $oldSlug)->first();

            if (! $row) {
                continue;
            }

            // Preserve the existing id — this is a data update, not a recreate.
            DB::table('services')->where('id', $row->id)->update([
                'slug'              => $data['slug'],
                'name'              => $data['name'],
                'short_description' => $data['short_description'],
                'cta_label'         => $data['cta_label'],
                'updated_at'        => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('services')) {
            return;
        }

        foreach ($this->mapping() as $oldSlug => $data) {
            $row = DB::table('services')->where('slug', $data['slug'])->first();

            if (! $row) {
                continue;
            }

            DB::table('services')->where('id', $row->id)->update([
                'slug'              => $oldSlug,
                'name'              => $this->legacyName($oldSlug),
                'short_description' => null,
                'cta_label'         => null,
                'updated_at'        => now(),
            ]);
        }
    }

    protected function legacyName(string $oldSlug): string
    {
        return match ($oldSlug) {
            'study-in-china-application-guide'   => 'Study In China – Application Guide',
            'study-in-china-complete-support'    => 'Study in China – Complete Application Support',
            'complete-china-success'             => 'Complete China Success – 1 Year Pathway',
            default                              => $oldSlug,
        };
    }
};
