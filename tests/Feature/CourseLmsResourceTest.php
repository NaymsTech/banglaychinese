<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\Pages\ListCourses;
use App\Filament\Resources\Lessons\Pages\CreateLesson;
use App\Filament\Resources\Lessons\Pages\ListLessons;
use App\Filament\Resources\Modules\Pages\EditModule;
use App\Filament\Resources\Modules\Pages\ListModules;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CourseLmsResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    public function test_admin_can_view_course_list_and_create_page(): void
    {
        $this->actingAs($this->admin())
            ->get(ListCourses::getUrl())
            ->assertOk();

        $this->actingAs($this->admin())
            ->get(CreateCourse::getUrl())
            ->assertOk();
    }

    public function test_admin_can_create_a_course_through_the_form(): void
    {
        $category = Category::create(['name' => 'HSK Prep', 'slug' => 'hsk-prep']);

        Livewire::actingAs($this->admin())
            ->test(CreateCourse::class)
            ->fillForm([
                'title' => 'HSK 1 Crash Course',
                'category_id' => (string) $category->id,
                'price' => 9500,
                'hsk_level' => '1',
                'duration_months' => 3,
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('courses', [
            'title' => 'HSK 1 Crash Course',
            'slug' => 'hsk-1-crash-course',
            'category_id' => $category->id,
            'price' => 9500,
            'is_published' => 1,
        ]);
    }

    public function test_admin_can_bulk_toggle_publish_from_the_list(): void
    {
        $draft = Course::create([
            'title' => 'HSK 1 Crash Course',
            'slug' => 'hsk-1-crash-course',
            'price' => 9500,
            'is_published' => false,
        ]);

        $published = Course::create([
            'title' => 'HSK 2 Intensive',
            'slug' => 'hsk-2-intensive',
            'price' => 0,
            'is_published' => true,
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListCourses::class)
            ->callTableBulkAction('togglePublish', [$draft->id, $published->id]);

        $this->assertSame(1, (int) $draft->fresh()->is_published);
        $this->assertSame(0, (int) $published->fresh()->is_published);
    }

    public function test_full_course_module_lesson_pages_render(): void
    {
        $category = Category::create(['name' => 'HSK Prep', 'slug' => 'hsk-prep']);
        $course = Course::create([
            'title' => 'HSK 1 Crash Course',
            'slug' => 'hsk-1-crash-course',
            'description' => '<p>Intro</p>',
            'price' => 9500,
            'category_id' => $category->id,
            'is_published' => true,
        ]);

        $moduleOne = $course->modules()->create(['title' => 'Basics', 'order' => 1]);
        $course->modules()->create(['title' => 'Speaking', 'order' => 2]);

        $moduleOne->lessons()->create([
            'title' => 'Introduction',
            'slug' => 'introduction',
            'content' => '<p>Welcome</p>',
            'order' => 1,
            'is_free_preview' => true,
        ]);
        $moduleOne->lessons()->create([
            'title' => 'Tones',
            'slug' => 'tones',
            'content' => '<p>Tones lesson</p>',
            'order' => 2,
        ]);

        $admin = $this->admin();

        // Course edit page mounts the Modules relation manager (with lesson counts + reorder).
        $this->actingAs($admin)
            ->get(EditCourse::getUrl(['record' => $course]))
            ->assertOk();

        // Module edit page mounts the Lessons relation manager.
        $this->actingAs($admin)
            ->get(EditModule::getUrl(['record' => $moduleOne]))
            ->assertOk();

        // Standalone list pages (withCount queries run here).
        foreach ([ListCourses::getUrl(), ListModules::getUrl(), ListLessons::getUrl(), ListCategories::getUrl()] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }

        // Lesson slugs are globally unique -> creating a duplicate must fail validation.
        $other = Module::create(['course_id' => $course->id, 'title' => 'Other', 'order' => 3]);

        Livewire::actingAs($admin)
            ->test(CreateLesson::class)
            ->fillForm([
                'module_id' => (string) $other->id,
                'title' => 'Introduction',
                'content' => '<p>Duplicate</p>',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }
}
