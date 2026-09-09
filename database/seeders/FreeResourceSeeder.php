<?php

namespace Database\Seeders;

use App\Models\FreeResource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class FreeResourceSeeder extends Seeder
{
    /**
     * Seed sample Free Resources for testing the public page and admin panel.
     *
     * The resources (including the dummy PDF files and the unverified YouTube
     * IDs) are demo content: they are created only in a local environment so
     * `php artisan db:seed --force` can never publish them on production.
     */
    public function run(): void
    {
        if (! app()->isLocal()) {
            return;
        }

        // Ensure directory exists
        Storage::disk('public')->makeDirectory('resources/pdfs');

        // Create dummy PDF files so download buttons don't 404
        $dummyContent = "%PDF-1.4\nDummy PDF content for testing Banglay Chinese Free Resources.";
        Storage::disk('public')->put('resources/pdfs/hsk1_vocab.pdf', $dummyContent);
        Storage::disk('public')->put('resources/pdfs/daily_phrases.pdf', $dummyContent);

        $resources = [
            [
                'title' => 'HSK 1 Essential Vocabulary List',
                'description' => 'A complete PDF guide containing the 150 most common words for the HSK 1 exam, including Pinyin and English translations.',
                'resource_type' => 'pdf',
                'file_path' => 'resources/pdfs/hsk1_vocab.pdf',
                'embed_url' => null,
                'category' => 'HSK 1',
                'is_published' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Mastering the 4 Chinese Tones',
                'description' => 'A beginner-friendly video explaining the 4 tones in Mandarin Chinese with audio examples and practice tips.',
                'resource_type' => 'video',
                'file_path' => null,
                'embed_url' => 'https://www.youtube.com/embed/0lGrrYj8hJg',
                'category' => 'Study Tips',
                'is_published' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Basic Chinese Sentence Structure',
                'description' => 'Learn the Subject-Verb-Object (SVO) structure in Mandarin and how to build your first sentences.',
                'resource_type' => 'video',
                'file_path' => null,
                'embed_url' => 'https://www.youtube.com/embed/5h2c1g1f1gI',
                'category' => 'Grammar',
                'is_published' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Top 50 Daily Conversational Phrases',
                'description' => 'A quick reference PDF for everyday greetings, numbers, and common phrases used in China.',
                'resource_type' => 'pdf',
                'file_path' => 'resources/pdfs/daily_phrases.pdf',
                'embed_url' => null,
                'category' => 'Vocabulary',
                'is_published' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'HSK 1 Practice Test (Audio & Text)',
                'description' => 'A sample mock test to help you prepare for the official HSK 1 examination.',
                'resource_type' => 'link',
                'file_path' => null,
                'embed_url' => 'https://www.chinesetest.cn',
                'category' => 'HSK 1',
                'is_published' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($resources as $resource) {
            FreeResource::updateOrCreate(['title' => $resource['title']], $resource);
        }
    }
}
