<?php

namespace Tests\Feature;

use App\Filament\Resources\MentorshipBookings\Pages\ListMentorshipBookings;
use App\Filament\Resources\ScholarshipApplications\Pages\EditScholarshipApplication;
use App\Filament\Resources\ScholarshipApplications\Pages\ListScholarshipApplications;
use App\Models\Category;
use App\Models\Course;
use App\Models\Mentor;
use App\Models\MentorshipBooking;
use App\Models\ScholarshipApplication;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class Phase2AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function student(): User
    {
        return User::factory()->create(['role' => 'student']);
    }

    // ---------- Scholarship journey status ----------

    public function test_admin_can_advance_an_application_through_the_journey(): void
    {
        $application = ScholarshipApplication::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '01711111111',
            'status' => 'converted',
            'journey_status' => 'new',
        ]);

        Livewire::actingAs($this->admin())
            ->test(EditScholarshipApplication::class, ['record' => $application->getRouteKey()])
            ->fillForm([
                'journey_status' => 'visa_processing',
                'admin_notes' => 'Transcripts verified; visa interview on Monday.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $application->refresh();

        $this->assertSame('visa_processing', $application->journey_status);
        $this->assertSame('Transcripts verified; visa interview on Monday.', $application->admin_notes);
    }

    public function test_journey_status_shows_as_color_coded_badge_in_the_list(): void
    {
        ScholarshipApplication::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '01711111111',
            'status' => 'new',
            'journey_status' => 'offer_received',
        ]);

        $this->actingAs($this->admin())
            ->get(ListScholarshipApplications::getUrl())
            ->assertOk()
            ->assertSee('Offer Received');
    }

    // ---------- Mentorship bookings bulk actions ----------

    public function test_admin_can_bulk_mark_bookings_as_completed(): void
    {
        $mentor = Mentor::create([
            'name' => 'Rafiq Sir',
            'expertise' => 'HSK 4 Prep',
            'is_available' => true,
        ]);
        $student = $this->student();

        $pending = MentorshipBooking::create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'scheduled_at' => now()->addDay(),
            'status' => 'pending',
        ]);
        $confirmed = MentorshipBooking::create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'scheduled_at' => now()->addDay(),
            'status' => 'confirmed',
        ]);
        $cancelled = MentorshipBooking::create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'scheduled_at' => now()->addDay(),
            'status' => 'cancelled',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListMentorshipBookings::class)
            ->callTableBulkAction('bulkComplete', [$pending, $confirmed, $cancelled])
            ->assertNotified();

        $this->assertSame('completed', $pending->fresh()->status);
        $this->assertSame('completed', $confirmed->fresh()->status);
        $this->assertSame('cancelled', $cancelled->fresh()->status);
    }

    public function test_admin_can_bulk_cancel_bookings(): void
    {
        $mentor = Mentor::create([
            'name' => 'Rafiq Sir',
            'expertise' => 'HSK 4 Prep',
            'is_available' => true,
        ]);
        $student = $this->student();

        $confirmed = MentorshipBooking::create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'scheduled_at' => now()->addDay(),
            'status' => 'confirmed',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListMentorshipBookings::class)
            ->callTableBulkAction('bulkCancel', [$confirmed])
            ->assertNotified();

        $this->assertSame('cancelled', $confirmed->fresh()->status);
    }

    // ---------- Course SEO ----------

    public function test_course_seo_fields_save_and_are_used_on_the_public_page(): void
    {
        $category = Category::create(['name' => 'HSK Prep', 'slug' => 'hsk-prep']);

        $course = Course::create([
            'title' => 'HSK 1 Crash Course',
            'slug' => 'hsk-1-crash-course',
            'description' => '<p>Learn HSK 1 fast.</p>',
            'price' => 9500,
            'category_id' => $category->id,
            'is_published' => true,
            'meta_title' => 'HSK 1 Crash Course — Study HSK 1 Online',
            'meta_description' => 'A focused 3-month HSK 1 course with live classes.',
            'og_image' => 'courses/og/hsk1.jpg',
        ]);

        $this->get('/courses/hsk-1-crash-course')
            ->assertOk()
            ->assertSee('HSK 1 Crash Course — Study HSK 1 Online')
            ->assertSee('A focused 3-month HSK 1 course with live classes.')
            ->assertSee('courses/og/hsk1.jpg');
    }

    // ---------- Global settings: footer + homepage meta ----------

    public function test_footer_copyright_text_is_rendered_from_settings(): void
    {
        SettingsService::set('footer_copyright_text', 'Copyright {year} {site}. All rights reserved.');
        SettingsService::flush();

        $this->get('/')
            ->assertOk()
            ->assertSee('All rights reserved.')
            ->assertSee((string) date('Y'));
    }

    public function test_homepage_meta_title_can_be_overridden_from_settings(): void
    {
        SettingsService::set('home_meta_title', 'Custom Homepage Meta Title');
        SettingsService::flush();

        $this->get('/')
            ->assertOk()
            ->assertSee('Custom Homepage Meta Title');
    }
}
