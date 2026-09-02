<?php

namespace Tests\Feature;

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactMessagesResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function message(array $overrides = []): ContactMessage
    {
        return ContactMessage::create(array_merge([
            'name' => 'Rahim Uddin',
            'phone' => '01811111111',
            'email' => 'rahim@example.com',
            'topic' => 'Admission query',
            'message' => 'Please send me details about MBBS in China.',
        ], $overrides));
    }

    public function test_admin_can_view_messages_list(): void
    {
        $this->message();

        $this->actingAs($this->admin())
            ->get(ListContactMessages::getUrl())
            ->assertOk()
            ->assertSee('Rahim Uddin')
            ->assertSee('Admission query');
    }

    public function test_admin_can_mark_selected_messages_as_read(): void
    {
        $first = $this->message();
        $second = $this->message(['name' => 'Karim', 'phone' => '01911111111']);

        Livewire::actingAs($this->admin())
            ->test(ListContactMessages::class)
            ->callTableBulkAction('markAsRead', [$first, $second])
            ->assertNotified();

        $this->assertTrue($first->fresh()->is_read);
        $this->assertTrue($second->fresh()->is_read);
    }

    public function test_admin_can_delete_a_message(): void
    {
        $message = $this->message();

        Livewire::actingAs($this->admin())
            ->test(ListContactMessages::class)
            ->callTableAction('delete', $message)
            ->assertNotified();

        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }
}
