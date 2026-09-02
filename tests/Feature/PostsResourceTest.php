<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_view_posts_list(): void
    {
        $this->actingAs($this->admin())
            ->get(ListPosts::getUrl())
            ->assertOk()
            ->assertSee('Blog Posts');
    }

    public function test_admin_can_create_a_published_post(): void
    {
        $category = Category::create(['name' => 'HSK Tips', 'slug' => 'hsk-tips']);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(CreatePost::class)
            ->fillForm([
                'title' => 'How to Pass HSK 2',
                'category_id' => (string) $category->id,
                'content' => '<p>Study daily.</p>',
                'is_published' => true,
                'published_at' => '2026-09-10 10:00:00',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('posts', [
            'title' => 'How to Pass HSK 2',
            'slug' => 'how-to-pass-hsk-2',
            'category_id' => $category->id,
            'user_id' => $admin->id,
            'is_published' => 1,
            'published_at' => '2026-09-10 10:00:00',
        ]);
    }
}
