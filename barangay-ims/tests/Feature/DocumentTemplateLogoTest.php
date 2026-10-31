<?php

namespace Tests\Feature;

use App\Models\DocumentTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTemplateLogoTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin User',
            'email' => 'admin' . uniqid() . '@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function templateWithLogo(string $path): DocumentTemplate
    {
        Storage::disk('public')->put($path, 'fake-image-content');

        return DocumentTemplate::create([
            'name' => 'Template ' . uniqid(),
            'title' => 'Barangay Certificate',
            'body' => 'Certificate body.',
            'logo_path' => $path,
            'is_active' => true,
        ]);
    }

    private function payload(DocumentTemplate $template, array $overrides = []): array
    {
        return array_merge([
            'name' => $template->name,
            'title' => 'Barangay Certificate',
            'body' => 'Certificate body.',
        ], $overrides);
    }

    public function test_uploading_a_replacement_logo_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        $template = $this->templateWithLogo('document-templates/logos/old.png');

        $this->actingAs($this->admin())
            ->put("/document-templates/{$template->id}", $this->payload($template, [
                'logo' => UploadedFile::fake()->image('new.png'),
            ]))
            ->assertRedirect();

        $template->refresh();
        $this->assertNotSame('document-templates/logos/old.png', $template->logo_path);
        Storage::disk('public')->assertMissing('document-templates/logos/old.png');
        Storage::disk('public')->assertExists($template->logo_path);
    }

    public function test_removing_a_logo_deletes_the_file(): void
    {
        Storage::fake('public');
        $template = $this->templateWithLogo('document-templates/logos/old.png');

        $this->actingAs($this->admin())
            ->put("/document-templates/{$template->id}", $this->payload($template, [
                'remove_logo' => '1',
            ]))
            ->assertRedirect();

        $template->refresh();
        $this->assertNull($template->logo_path);
        Storage::disk('public')->assertMissing('document-templates/logos/old.png');
    }

    public function test_uploading_and_removing_at_once_keeps_the_upload_and_leaves_no_orphans(): void
    {
        Storage::fake('public');
        $template = $this->templateWithLogo('document-templates/logos/old.png');

        $this->actingAs($this->admin())
            ->put("/document-templates/{$template->id}", $this->payload($template, [
                'logo' => UploadedFile::fake()->image('new.png'),
                'remove_logo' => '1',
            ]))
            ->assertRedirect();

        $template->refresh();
        $this->assertNotNull($template->logo_path);

        $files = Storage::disk('public')->allFiles('document-templates/logos');
        $this->assertCount(1, $files);
        $this->assertSame($template->logo_path, str_replace('\\', '/', $files[0]));
    }
}
