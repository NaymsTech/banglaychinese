<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove the now-orphaned Study-in-China service categories. These are
     * only deleted when no course references them, to avoid removing in-use data.
     */
    protected function serviceSlugs(): array
    {
        return ['study-in-china', 'scholarship-guidance'];
    }

    public function up(): void
    {
        if (! Schema::hasTable('categories') || ! Schema::hasTable('courses')) {
            return;
        }

        $orphaned = DB::table('categories')
            ->whereIn('slug', $this->serviceSlugs())
            ->whereNotIn('id', function ($q) {
                $q->select('category_id')->from('courses')->whereNotNull('category_id');
            })
            ->pluck('id');

        if ($orphaned->isNotEmpty()) {
            DB::table('categories')->whereIn('id', $orphaned)->delete();
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        $existing = DB::table('categories')->whereIn('slug', $this->serviceSlugs())->pluck('slug')->all();

        $categories = [
            'study-in-china' => [
                'name' => 'Study in China',
                'description' => 'Complete application support, guidance, and pathway programs for studying in China.',
            ],
            'scholarship-guidance' => [
                'name' => 'Scholarship Guidance',
                'description' => 'Fast-track prep and guidance for Chinese university scholarships and admission deadlines.',
            ],
        ];

        foreach ($categories as $slug => $data) {
            if (! in_array($slug, $existing, true)) {
                DB::table('categories')->insert([
                    'name' => $data['name'],
                    'slug' => $slug,
                    'description' => $data['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
