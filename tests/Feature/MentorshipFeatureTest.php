<?php

namespace Tests\Feature;

use App\Filament\Resources\Mentors\Pages\CreateMentor;
use App\Filament\Resources\Mentors\Pages\EditMentor;
use App\Filament\Resources\Mentors\Pages\ListMentors;
use App\Filament\Resources\MentorshipBookings\Pages\CreateMentorshipBooking;
use App\Filament\Resources\MentorshipBookings\Pages\ListMentorshipBookings;
use App\Models\Mentor;
use App\Models\MentorAvailability;
use App\Models\MentorshipBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MentorshipFeatureTest extends TestCase
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

    protected function mentor(): Mentor
    {
        return Mentor::create([
            'name' => 'Rafiq Sir',
            'expertise' => 'HSK 4 Prep',
            'hourly_rate' => 800,
            'bio' => '<p>10 years of experience.</p>',
            'is_available' => true,
        ]);
    }

    public function test_admin_can_create_a_mentor(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateMentor::class)
            ->fillForm([
                'name' => 'Rafiq Sir',
                'expertise' => 'HSK 4 Prep',
                'hourly_rate' => 800,
                'bio' => '<p>10 years of experience.</p>',
                'is_available' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('mentors', [
            'name' => 'Rafiq Sir',
            'expertise' => 'HSK 4 Prep',
            'hourly_rate' => 800,
            'is_available' => 1,
        ]);
    }

    public function test_mentor_edit_page_shows_availability_relation_manager(): void
    {
        $mentor = $this->mentor();

        $mentor->availabilities()->create([
            'day_of_week' => 1, // Monday
            'start_time' => '18:00:00',
            'end_time' => '20:00:00',
        ]);

        $this->actingAs($this->admin())
            ->get(EditMentor::getUrl(['record' => $mentor]))
            ->assertOk()
            ->assertSee('Availabilities');
    }

    public function test_admin_can_create_a_booking(): void
    {
        $mentor = $this->mentor();
        $student = $this->student();

        Livewire::actingAs($this->admin())
            ->test(CreateMentorshipBooking::class)
            ->fillForm([
                'student_id' => (string) $student->id,
                'mentor_id' => (string) $mentor->id,
                'scheduled_at' => '2026-10-05 18:00:00',
                'notes' => 'Student wants HSK 4 speaking practice.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('mentorship_bookings', [
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'status' => 'pending',
            'meeting_link' => null,
        ]);
    }

    public function test_admin_can_confirm_a_pending_booking_with_meeting_link(): void
    {
        $booking = MentorshipBooking::create([
            'student_id' => $this->student()->id,
            'mentor_id' => $this->mentor()->id,
            'scheduled_at' => now()->addDay(),
            'status' => 'pending',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListMentorshipBookings::class)
            ->callTableAction('confirm', $booking, data: [
                'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            ])
            ->assertNotified();

        $booking->refresh();

        $this->assertSame('confirmed', $booking->status);
        $this->assertSame('https://meet.google.com/abc-defg-hij', $booking->meeting_link);
    }

    public function test_admin_can_cancel_a_booking(): void
    {
        $booking = MentorshipBooking::create([
            'student_id' => $this->student()->id,
            'mentor_id' => $this->mentor()->id,
            'scheduled_at' => now()->addDay(),
            'status' => 'pending',
        ]);

        Livewire::actingAs($this->admin())
            ->test(ListMentorshipBookings::class)
            ->callTableAction('cancel', $booking)
            ->assertNotified();

        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_pages_render(): void
    {
        $this->actingAs($this->admin())
            ->get(ListMentors::getUrl())
            ->assertOk();

        $this->actingAs($this->admin())
            ->get(ListMentorshipBookings::getUrl())
            ->assertOk();
    }
}
