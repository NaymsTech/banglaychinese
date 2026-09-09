<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailTemplates\Pages\CreateEmailTemplate;
use App\Filament\Resources\EmailTemplates\Pages\ListEmailTemplates;
use App\Filament\Resources\EmailTemplates\Tables\EmailTemplatesTable;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmailTemplatesResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }

    protected function template(array $overrides = []): EmailTemplate
    {
        return EmailTemplate::factory()->create(array_merge([
            'name' => 'Product Download Ready',
            'key' => 'product_approved',
            'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
            'variables' => ['student_name', 'product_title', 'download_link'],
        ], $overrides));
    }

    public function test_admin_can_open_the_template_list(): void
    {
        $this->template();

        $this->actingAs($this->admin())
            ->get(ListEmailTemplates::getUrl())
            ->assertOk()
            ->assertSee('Product Download Ready')
            ->assertSee('product_approved', false)
            ->assertSee('Transactional');
    }

    public function test_admin_can_create_a_template_with_variables(): void
    {
        Livewire::actingAs($this->admin())
            ->test(CreateEmailTemplate::class)
            ->fillForm([
                'name' => 'Course Approved',
                'key' => 'course_approved',
                'category' => EmailTemplate::CATEGORY_TRANSACTIONAL,
                'subject' => 'Hi {student_name}, your course is approved!',
                'body' => '<h1>Congratulations {student_name}!</h1><p>Your course is ready.</p>',
                'from_address' => 'no-reply@banglaychinese.com',
                'from_name' => 'Banglay Chinese Courses',
                'variables' => ['student_name'],
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $template = EmailTemplate::where('key', 'course_approved')->firstOrFail();

        $this->assertSame('Course Approved', $template->name);
        $this->assertSame(['student_name'], $template->variables);
        $this->assertSame('no-reply@banglaychinese.com', $template->from_address);
        $this->assertSame('Banglay Chinese Courses', $template->from_name);
        $this->assertTrue($template->is_active);
    }

    public function test_preview_action_builds_fields_for_each_template_variable(): void
    {
        $template = $this->template(['variables' => ['student_name', 'download_link']]);

        Livewire::actingAs($this->admin())
            ->test(ListEmailTemplates::class)
            ->mountTableAction('preview', $template)
            ->assertFormFieldExists('value_student_name')
            ->assertFormFieldExists('value_download_link');
    }

    public function test_preview_renders_the_selected_template_through_the_branded_shell(): void
    {
        $template = $this->template([
            'subject' => 'Hi {student_name}, your {product_title} is ready',
            'body' => '<h2>Your download is ready</h2><p>Hello {student_name}, welcome to {product_title}.</p>',
            'variables' => ['student_name', 'product_title'],
        ]);

        // The preview modal still mounts with its per-variable fields.
        Livewire::actingAs($this->admin())
            ->test(ListEmailTemplates::class)
            ->mountTableAction('preview', $template)
            ->assertFormFieldExists('value_student_name')
            ->assertFormFieldExists('value_product_title');

        // The preview's HTML is composed by the same canonical shell path as
        // real delivery (EmailService::renderForDelivery via previewHtml).
        $html = EmailTemplatesTable::previewHtml($template, [
            'student_name' => 'Demo Student',
            'product_title' => 'HSK 1 E-Book',
        ]);

        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('Learn Chinese in Bangla • Study in China', $html);
        $this->assertStringContainsString('assets/logo-full.png', $html);
        $this->assertStringContainsString('#007A3D', $html);
        $this->assertStringContainsString('Demo Student', $html);
        $this->assertStringContainsString('HSK 1 E-Book', $html);
        $this->assertStringContainsString('Your download is ready', $html);
        $this->assertStringNotContainsString('#0f5132', $html);
    }
}
