<?php

namespace Tests\Feature;

use App\Models\FreeResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreeResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdf = FreeResource::create([
            'title' => 'HSK 1 Essential Vocabulary List',
            'description' => 'The 150 most common HSK 1 words.',
            'resource_type' => 'pdf',
            'file_path' => 'resources/pdfs/hsk1_vocab.pdf',
            'category' => 'HSK 1',
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->video = FreeResource::create([
            'title' => 'Mastering the 4 Chinese Tones',
            'description' => 'Beginner-friendly tone practice.',
            'resource_type' => 'video',
            'embed_url' => 'https://www.youtube.com/embed/0lGrrYj8hJg',
            'category' => 'Study Tips',
            'is_published' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_public_page_lists_published_resources_grouped_by_category(): void
    {
        $this->get('/free-resources')
            ->assertOk()
            ->assertSee('Free Study Resources')
            ->assertSee('HSK 1 Essential Vocabulary List')
            ->assertSee('Mastering the 4 Chinese Tones')
            ->assertSee('Study Tips');
    }

    public function test_videos_are_visible_to_everyone(): void
    {
        $this->get('/free-resources')
            ->assertOk()
            ->assertSee('https://www.youtube.com/embed/0lGrrYj8hJg', false);
    }

    public function test_pdf_cards_link_directly_to_the_public_file_for_everyone(): void
    {
        $this->get('/free-resources')
            ->assertOk()
            ->assertSee('storage/resources/pdfs/hsk1_vocab.pdf', false)
            ->assertSee('Download PDF')
            ->assertDontSee('Login to Download');
    }

    public function test_unpublished_resources_are_hidden_from_the_public_page(): void
    {
        FreeResource::create([
            'title' => 'Draft Grammar Cheat Sheet',
            'resource_type' => 'pdf',
            'file_path' => 'resources/pdfs/draft.pdf',
            'category' => 'Grammar',
            'is_published' => false,
            'sort_order' => 5,
        ]);

        $this->get('/free-resources')
            ->assertOk()
            ->assertDontSee('Draft Grammar Cheat Sheet');
    }
}
